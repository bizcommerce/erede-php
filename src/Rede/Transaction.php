<?php

declare(strict_types=1);

namespace Rede;

use ArrayIterator;
use DateTimeImmutable;
use InvalidArgumentException;
use Rede\Enum\Mpi;
use Rede\Enum\OnFailure;
use Rede\Enum\TransactionKind;
use Rede\Enum\TransactionOrigin;

class Transaction implements RedeSerializable, RedeUnserializable
{
    private ?int $amount = null;

    private ?Additional $additional = null;

    private ?Antifraud $antifraud = null;

    private ?bool $antifraudRequired = null;

    private ?Authorization $authorization = null;

    private ?string $authorizationCode = null;

    private ?Brand $brand = null;

    private ?string $brandTid = null;

    private ?string $cancelId = null;

    private bool|Capture|null $capture = null;

    private ?string $cardBin = null;

    private ?string $cardHolderName = null;

    private ?string $cardNumber = null;

    private ?string $cardToken = null;

    private ?Cart $cart = null;

    private ?QrCode $qrCode = null;

    private ?QrCodeResponse $qrCodeResponse = null;

    private ?DateTimeImmutable $dateTime = null;

    private ?int $distributorAffiliation = null;

    private ?string $expirationMonth = null;

    private ?string $expirationYear = null;

    private ?Iata $iata = null;

    private ?int $installments = null;

    private ?TransactionKind $kind = null;

    private ?string $last4 = null;

    private ?string $nsu = null;

    private ?TransactionOrigin $origin = null;

    private ?string $paymentFacilitatorID = null;

    private ?string $reference = null;

    private ?DateTimeImmutable $refundDateTime = null;

    private ?string $refundId = null;

    /**
     * @var array<int, Refund>|null
     */
    private ?array $refunds = null;

    private ?DateTimeImmutable $requestDateTime = null;

    private ?string $returnCode = null;

    private ?string $returnMessage = null;

    private ?string $securityCode = null;

    private ?string $softDescriptor = null;

    private ?int $storageCard = null;

    private ?SubMerchant $subMerchant = null;

    private ?bool $subscription = null;

    private ?ThreeDSecure $threeDSecure = null;

    private ?string $tid = null;

    /**
     * @var array<int, Url>|null
     */
    private ?array $urls = null;

    public function __construct(int|float|null $amount = null, ?string $reference = null)
    {
        $this->setAmount($amount);
        $this->setReference($reference);
    }

    public function addUrl(string $url, \Rede\Enum\UrlKind $kind = \Rede\Enum\UrlKind::Callback): static
    {
        $this->urls[] = new Url($url, $kind);

        return $this;
    }

    public function additional(): Additional
    {
        return $this->additional = new Additional();
    }

    public function antifraud(Environment $environment): Cart
    {
        $cart = new Cart();
        $cart->setEnvironment($environment);

        $this->setAntifraudRequired(true);
        $this->setCart($cart);

        return $cart;
    }

    public function capture(bool $capture = true): static
    {
        if (!$capture && $this->kind === TransactionKind::Debit) {
            throw new InvalidArgumentException('Debit transactions will always be captured');
        }

        $this->capture = $capture;

        return $this;
    }

    public function creditCard(
        string $cardNumber,
        string $securityCode,
        string $expirationMonth,
        string $expirationYear,
        string $holderName,
    ): static {
        return $this->setCard($cardNumber, $securityCode, $expirationMonth, $expirationYear, $holderName, TransactionKind::Credit);
    }

    public function debitCard(
        string $cardNumber,
        string $securityCode,
        string $expirationMonth,
        string $expirationYear,
        string $holderName,
    ): static {
        $this->capture(true);

        return $this->setCard($cardNumber, $securityCode, $expirationMonth, $expirationYear, $holderName, TransactionKind::Debit);
    }

    public function jsonSerialize(): mixed
    {
        $capture = is_bool($this->capture) ? ($this->capture ? 'true' : 'false') : null;

        return array_filter([
            'capture' => $capture,
            'antifraudRequired' => $this->antifraudRequired,
            'cart' => $this->cart,
            'kind' => $this->kind,
            'threeDSecure' => $this->threeDSecure,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'installments' => $this->installments,
            'cardHolderName' => $this->cardHolderName,
            'cardNumber' => $this->cardNumber,
            'cardToken' => $this->cardToken,
            'expirationMonth' => $this->expirationMonth,
            'expirationYear' => $this->expirationYear,
            'securityCode' => $this->securityCode,
            'softDescriptor' => $this->softDescriptor,
            'subscription' => $this->subscription,
            'origin' => $this->origin,
            'distributorAffiliation' => $this->distributorAffiliation,
            'storageCard' => $this->storageCard,
            'urls' => $this->urls,
            'iata' => $this->iata,
            'qrCode' => $this->qrCode,
            'additional' => $this->additional,
            'subMerchant' => $this->subMerchant,
            'brandTid' => $this->brandTid,
            'paymentFacilitatorID' => $this->paymentFacilitatorID,
        ], static fn ($value): bool => $value !== null);
    }

    public function jsonUnserialize(string $serialized): static
    {
        $properties = json_decode($serialized);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(sprintf('JSON: %s', json_last_error_msg()));
        }

        foreach (get_object_vars($properties) as $property => $value) {
            match (true) {
                $property === 'links' => null,
                $property === 'refunds' && is_array($value) => $this->refunds = array_map(Refund::create(...), $value),
                $property === 'urls' && is_array($value) => $this->hydrateUrls($value),
                $property === 'capture' && is_object($value) => $this->capture = Capture::create($value),
                $property === 'authorization' && is_object($value) => $this->authorization = Authorization::create($value),
                $property === 'threeDSecure' && is_object($value) => $this->threeDSecure = ThreeDSecure::create($value),
                $property === 'antifraud' && is_object($value) => $this->antifraud = Antifraud::create($value),
                $property === 'qrCodeResponse' && is_object($value) => $this->qrCodeResponse = QrCodeResponse::create($value),
                $property === 'brand' && is_object($value) => $this->brand = Brand::create($value),
                in_array($property, ['requestDateTime', 'dateTime', 'refundDateTime'], true) => $this->{$property} = new DateTimeImmutable($value),
                $property === 'kind' => $this->kind = TransactionKind::tryFrom($value),
                $property === 'origin' => $this->origin = TransactionOrigin::tryFrom((int) $value),
                property_exists($this, $property) => $this->assignScalar($property, $value),
                default => null,
            };
        }

        return $this;
    }

    /**
     * @param array<int, \stdClass> $urls
     */
    private function hydrateUrls(array $urls): void
    {
        $this->urls = array_map(
            static fn ($url): Url => new Url($url->url, \Rede\Enum\UrlKind::tryFrom($url->kind) ?? \Rede\Enum\UrlKind::Callback),
            $urls
        );
    }

    private function assignScalar(string $property, mixed $value): void
    {
        // Only declared, non-relational scalars reach here; coerce loosely so a
        // gateway type quirk never throws.
        $this->{$property} = match ($property) {
            'amount', 'installments', 'storageCard', 'distributorAffiliation' => is_numeric($value) ? (int) $value : $value,
            'subscription', 'antifraudRequired' => (bool) $value,
            default => is_scalar($value) ? (string) $value : $value,
        };
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getAdditional(): ?Additional
    {
        return $this->additional;
    }

    public function getAntifraud(): Antifraud
    {
        return $this->antifraud ?? new Antifraud();
    }

    public function getAuthorization(): ?Authorization
    {
        return $this->authorization;
    }

    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(Brand $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getBrandTid(): ?string
    {
        return $this->brandTid;
    }

    public function setBrandTid(string $brandTid): static
    {
        $this->brandTid = $brandTid;

        return $this;
    }

    public function getCancelId(): ?string
    {
        return $this->cancelId;
    }

    public function getCapture(): bool|Capture|null
    {
        return $this->capture;
    }

    public function getCardBin(): ?string
    {
        return $this->cardBin;
    }

    public function getCardHolderName(): ?string
    {
        return $this->cardHolderName;
    }

    public function getCardNumber(): ?string
    {
        return $this->cardNumber;
    }

    public function getCardToken(): ?string
    {
        return $this->cardToken;
    }

    public function cardToken(string $cardToken): static
    {
        $this->cardToken = $cardToken;
        $this->storageCard = 2;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function getDateTime(): ?DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function getDistributorAffiliation(): ?int
    {
        return $this->distributorAffiliation;
    }

    public function getExpirationMonth(): ?string
    {
        return $this->expirationMonth;
    }

    public function getExpirationYear(): ?string
    {
        return $this->expirationYear;
    }

    public function getIata(): ?Iata
    {
        return $this->iata;
    }

    public function getInstallments(): ?int
    {
        return $this->installments;
    }

    public function getKind(): ?TransactionKind
    {
        return $this->kind;
    }

    public function getLast4(): ?string
    {
        return $this->last4;
    }

    public function getNsu(): ?string
    {
        return $this->nsu;
    }

    public function getOrigin(): ?TransactionOrigin
    {
        return $this->origin;
    }

    public function getPaymentFacilitatorID(): ?string
    {
        return $this->paymentFacilitatorID;
    }

    public function setPaymentFacilitatorID(string $paymentFacilitatorID): static
    {
        $this->paymentFacilitatorID = $paymentFacilitatorID;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getRefundDateTime(): ?DateTimeImmutable
    {
        return $this->refundDateTime;
    }

    public function getRefundId(): ?string
    {
        return $this->refundId;
    }

    /**
     * @return array<int, Refund>|null
     */
    public function getRefunds(): ?array
    {
        return $this->refunds;
    }

    public function getRequestDateTime(): ?DateTimeImmutable
    {
        return $this->requestDateTime;
    }

    public function getReturnCode(): ?string
    {
        return $this->returnCode;
    }

    public function getReturnMessage(): ?string
    {
        return $this->returnMessage;
    }

    public function getSecurityCode(): ?string
    {
        return $this->securityCode;
    }

    public function getSoftDescriptor(): ?string
    {
        return $this->softDescriptor;
    }

    public function getStorageCard(): ?int
    {
        return $this->storageCard;
    }

    public function getSubMerchant(): ?SubMerchant
    {
        return $this->subMerchant;
    }

    public function setSubMerchant(SubMerchant $subMerchant): static
    {
        $this->subMerchant = $subMerchant;

        return $this;
    }

    public function mcc(string $mcc, string $city, string $country): static
    {
        return $this->setSubMerchant(new SubMerchant($mcc, $city, $country));
    }

    public function isAntifraudRequired(): ?bool
    {
        return $this->antifraudRequired;
    }

    public function iata(string $code, string $departureTax): static
    {
        return $this->setIata($code, $departureTax);
    }

    public function isSubscription(): ?bool
    {
        return $this->subscription;
    }

    /**
     * Turns this into a Pix QR Code request (kind=pix) with the given expiration
     * (YYYY-MM-DDThh:mm:ss). Submit it through eRede::create().
     */
    public function pix(string $dateTimeExpiration): static
    {
        $this->kind = TransactionKind::Pix;
        $this->qrCode = new QrCode($dateTimeExpiration);

        return $this;
    }

    public function getQrCode(): ?QrCode
    {
        return $this->qrCode;
    }

    public function getQrCodeResponse(): ?QrCodeResponse
    {
        return $this->qrCodeResponse;
    }

    public function getThreeDSecure(): ThreeDSecure
    {
        return $this->threeDSecure ?? new ThreeDSecure();
    }

    public function getTid(): ?string
    {
        return $this->tid;
    }

    /**
     * @return ArrayIterator<int, Url>
     */
    public function getUrlsIterator(): ArrayIterator
    {
        return new ArrayIterator($this->urls ?? []);
    }

    public function setIata(string $code, string $departureTax): static
    {
        $this->iata = (new Iata())->setCode($code)->setDepartureTax($departureTax);

        return $this;
    }

    public function setAmount(int|float|null $amount): static
    {
        // round() before the int cast avoids float truncation (25.01 -> 2501).
        $this->amount = (int) round((float) $amount * 100);

        return $this;
    }

    public function setAntifraudRequired(bool $antifraudRequired): static
    {
        $this->antifraudRequired = $antifraudRequired;

        return $this;
    }

    public function setCard(
        string $cardNumber,
        string $securityCode,
        string $expirationMonth,
        string $expirationYear,
        string $cardHolderName,
        TransactionKind $kind,
    ): static {
        $this->cardNumber = $cardNumber;
        $this->securityCode = $securityCode;
        $this->expirationMonth = $expirationMonth;
        $this->expirationYear = $expirationYear;
        $this->cardHolderName = $cardHolderName;
        $this->kind = $kind;

        return $this;
    }

    public function setCardHolderName(string $cardHolderName): static
    {
        $this->cardHolderName = $cardHolderName;

        return $this;
    }

    public function setCardNumber(string $cardNumber): static
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    public function setCart(Cart $cart): static
    {
        $this->cart = $cart;

        return $this;
    }

    public function setDistributorAffiliation(int $distributorAffiliation): static
    {
        $this->distributorAffiliation = $distributorAffiliation;

        return $this;
    }

    public function setExpirationMonth(string $expirationMonth): static
    {
        $this->expirationMonth = $expirationMonth;

        return $this;
    }

    public function setExpirationYear(string $expirationYear): static
    {
        $this->expirationYear = $expirationYear;

        return $this;
    }

    public function setInstallments(int $installments): static
    {
        $this->installments = $installments;

        return $this;
    }

    public function setKind(TransactionKind $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function setOrigin(TransactionOrigin $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function setSecurityCode(string $securityCode): static
    {
        $this->securityCode = $securityCode;

        return $this;
    }

    public function setSoftDescriptor(string $softDescriptor): static
    {
        $this->softDescriptor = $softDescriptor;

        return $this;
    }

    public function setStorageCard(int $storageCard): static
    {
        $this->storageCard = $storageCard;

        return $this;
    }

    public function setSubscription(bool $subscription): static
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function setTid(string $tid): static
    {
        $this->tid = $tid;

        return $this;
    }

    public function threeDSecure(?Device $device = null, OnFailure $onFailure = OnFailure::Decline, Mpi $mpi = Mpi::Rede): static
    {
        $this->threeDSecure = new ThreeDSecure($device, $onFailure, $mpi);

        return $this;
    }
}
