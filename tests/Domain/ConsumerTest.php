<?php

declare(strict_types=1);

namespace Rede\Tests\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Consumer;
use Rede\Enum\Gender;
use Rede\Enum\PhoneType;
use Rede\Phone;

#[CoversClass(Consumer::class)]
final class ConsumerTest extends TestCase
{
    #[Test]
    public function the_constructor_populates_the_identity_fields(): void
    {
        $consumer = new Consumer('Jane Roe', 'jane@example.test', '00000000000');

        self::assertSame('Jane Roe', $consumer->getName());
        self::assertSame('jane@example.test', $consumer->getEmail());
        self::assertSame('00000000000', $consumer->getCpf());
    }

    #[Test]
    public function add_document_accumulates_typed_documents(): void
    {
        $consumer = new Consumer('Jane', 'jane@example.test', '0');

        $returned = $consumer->addDocument('CPF', '11122233344');
        $consumer->addDocument('RG', '1234567');

        self::assertSame($consumer, $returned);
        $documents = iterator_to_array($consumer->getDocumentsIterator());
        self::assertCount(2, $documents);
        self::assertSame('CPF', $documents[0]->type);
        self::assertSame('11122233344', $documents[0]->number);
    }

    #[Test]
    public function documents_iterator_is_empty_when_none_were_added(): void
    {
        $consumer = new Consumer('Jane', 'jane@example.test', '0');

        self::assertCount(0, iterator_to_array($consumer->getDocumentsIterator()));
    }

    #[Test]
    public function phone_builds_attaches_and_defaults_to_cellphone(): void
    {
        $consumer = new Consumer('Jane', 'jane@example.test', '0');

        $returned = $consumer->phone('11', '999998888');

        self::assertSame($consumer, $returned);
        self::assertInstanceOf(Phone::class, $consumer->getPhone());
        self::assertSame('11', $consumer->getPhone()->getDdd());
        self::assertSame(PhoneType::Cellphone, $consumer->getPhone()->getType());
    }

    #[Test]
    public function gender_is_a_typed_enum(): void
    {
        $consumer = (new Consumer('Jane', 'jane@example.test', '0'))->setGender(Gender::Female);

        self::assertSame(Gender::Female, $consumer->getGender());
        self::assertSame('M', Gender::Male->value);
    }
}
