<?php

declare(strict_types=1);

namespace Rede\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Authorization;
use Rede\Enum\Mpi;
use Rede\Enum\OnFailure;
use Rede\Enum\TransactionKind;
use Rede\Refund;
use Rede\Tests\Support\Fixtures;
use Rede\Transaction;
use Rede\Url;

#[CoversClass(Transaction::class)]
final class TransactionTest extends TestCase
{
    use Fixtures;

    #[Test]
    public function the_constructor_scales_the_amount_to_cents(): void
    {
        self::assertSame(2500, (new Transaction(25.0))->getAmount());
        self::assertSame(2550, (new Transaction(25.5))->getAmount());
        self::assertSame(0, (new Transaction())->getAmount());
    }

    #[Test]
    public function set_amount_rounds_fractional_cents_instead_of_truncating(): void
    {
        self::assertSame(2501, (new Transaction(25.01))->getAmount());
        self::assertSame(9999, (new Transaction(99.99))->getAmount());
    }

    #[Test]
    public function it_is_json_serializable_so_nested_objects_keep_their_data(): void
    {
        $transaction = new Transaction(25.0, 'ref-1');
        $transaction->threeDSecure();
        $transaction->addUrl('https://example.test/cb');

        self::assertInstanceOf(JsonSerializable::class, $transaction);

        $encoded = json_decode(json_encode($transaction), true);

        self::assertSame('ref-1', $encoded['reference']);
        self::assertSame(2500, $encoded['amount']);
        self::assertTrue($encoded['threeDSecure']['embedded']);
        self::assertSame('https://example.test/cb', $encoded['urls'][0]['url']);
    }

    #[Test]
    public function json_serialize_drops_nulls_and_keeps_scalar_fields(): void
    {
        $transaction = new Transaction(25.0, 'ref-1');

        self::assertSame(['reference' => 'ref-1', 'amount' => 2500], $transaction->jsonSerialize());
    }

    #[Test]
    public function json_serialize_renders_capture_as_a_string_boolean_and_kind_as_its_enum_value(): void
    {
        $captured = (new Transaction(25.0, 'ref-1'))->creditCard('5448280000000007', '123', '12', '2030', 'JOHN')->capture(true);
        $encoded = json_decode(json_encode($captured), true);

        self::assertSame('true', $encoded['capture']);
        self::assertSame('credit', $encoded['kind']);

        $notCaptured = (new Transaction(25.0, 'ref-1'))->capture(false);
        self::assertSame('false', $notCaptured->jsonSerialize()['capture']);
    }

    #[Test]
    public function json_unserialize_hydrates_scalars_dates_and_nested_objects(): void
    {
        $transaction = new Transaction();

        $returned = $transaction->jsonUnserialize(self::fixture('transaction_authorized.json'));

        self::assertSame($transaction, $returned);
        self::assertSame('100120000000000001', $transaction->getTid());
        self::assertSame('00', $transaction->getReturnCode());
        self::assertSame(TransactionKind::Credit, $transaction->getKind());
        self::assertInstanceOf(DateTimeImmutable::class, $transaction->getDateTime());
        self::assertInstanceOf(DateTimeImmutable::class, $transaction->getRequestDateTime());

        $authorization = $transaction->getAuthorization();
        self::assertInstanceOf(Authorization::class, $authorization);
        self::assertSame('123456', $authorization->getAuthorizationCode());
        self::assertSame('Authorized', $authorization->getStatus());
        self::assertSame(2500, $authorization->getAmount());
    }

    #[Test]
    public function json_unserialize_builds_a_list_of_refunds(): void
    {
        $transaction = new Transaction();
        $transaction->jsonUnserialize(self::fixture('transaction_with_refunds.json'));

        $refunds = $transaction->getRefunds();
        self::assertCount(2, $refunds);
        self::assertContainsOnlyInstancesOf(Refund::class, $refunds);
        self::assertSame('refund-aaa-111', $refunds[0]->getRefundId());
        self::assertSame('PENDING', $refunds[0]->getStatus());
        self::assertSame(2500, $refunds[0]->getAmount());
    }

    #[Test]
    public function json_unserialize_rejects_malformed_json(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Transaction())->jsonUnserialize('{not valid json');
    }

    #[Test]
    public function add_url_appends_url_objects_and_is_fluent(): void
    {
        $transaction = new Transaction(25.0, 'ref-1');

        $returned = $transaction->addUrl('https://example.test/callback');

        self::assertSame($transaction, $returned);
        $urls = iterator_to_array($transaction->getUrlsIterator());
        self::assertCount(1, $urls);
        self::assertInstanceOf(Url::class, $urls[0]);
        self::assertSame('https://example.test/callback', $urls[0]->getUrl());
    }

    #[Test]
    public function credit_card_sets_the_card_fields_and_credit_kind(): void
    {
        $transaction = (new Transaction(25.0))->creditCard('5448280000000007', '123', '12', '2030', 'JOHN DOE');

        self::assertSame(TransactionKind::Credit, $transaction->getKind());
        self::assertSame('5448280000000007', $transaction->getCardNumber());
        self::assertSame('123', $transaction->getSecurityCode());
        self::assertSame('12', $transaction->getExpirationMonth());
        self::assertSame('2030', $transaction->getExpirationYear());
        self::assertSame('JOHN DOE', $transaction->getCardHolderName());
    }

    #[Test]
    public function debit_card_forces_capture_and_debit_kind(): void
    {
        $transaction = (new Transaction(25.0))->debitCard('5448280000000007', '123', '12', '2030', 'JOHN DOE');

        self::assertSame(TransactionKind::Debit, $transaction->getKind());
        self::assertTrue($transaction->getCapture());
    }

    #[Test]
    public function a_debit_transaction_cannot_opt_out_of_capture(): void
    {
        $transaction = (new Transaction(25.0))->debitCard('5448280000000007', '123', '12', '2030', 'JOHN DOE');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Debit transactions will always be captured');

        $transaction->capture(false);
    }

    #[Test]
    public function three_d_secure_configures_and_returns_the_object(): void
    {
        $transaction = (new Transaction(25.0))->threeDSecure(onFailure: OnFailure::Continue, mpi: Mpi::ThirdParty);

        $threeDSecure = $transaction->getThreeDSecure();
        self::assertSame(OnFailure::Continue, $threeDSecure->getOnFailure());
        self::assertFalse($threeDSecure->isEmbedded());
    }

    #[Test]
    public function iata_builds_the_iata_block(): void
    {
        $transaction = (new Transaction(25.0))->iata('TAX-CODE', '50');

        self::assertSame('TAX-CODE', $transaction->getIata()->getCode());
        self::assertSame('50', $transaction->getIata()->getDepartureTax());
    }

    #[Test]
    public function card_token_sets_the_token_and_marks_the_card_stored(): void
    {
        $transaction = (new Transaction(25.0))->cardToken('tok-abc');

        self::assertSame('tok-abc', $transaction->getCardToken());
        self::assertSame(2, $transaction->getStorageCard());
        self::assertSame('tok-abc', $transaction->jsonSerialize()['cardToken']);
    }
}
