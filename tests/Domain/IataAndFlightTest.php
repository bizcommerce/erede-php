<?php

declare(strict_types=1);

namespace Rede\Tests\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rede\Enum\PhoneType;
use Rede\Flight;
use Rede\Iata;
use Rede\Passenger;
use Rede\Phone;

#[CoversClass(Iata::class)]
#[CoversClass(Flight::class)]
#[CoversClass(Passenger::class)]
final class IataAndFlightTest extends TestCase
{
    private function flight(): Flight
    {
        return new Flight('AB123', 'GRU', 'JFK', '2024-02-01');
    }

    #[Test]
    public function iata_accumulates_flights(): void
    {
        $iata = (new Iata())->setCode('TAX')->setDepartureTax('50');
        $iata->addFlight($this->flight())->addFlight($this->flight());

        self::assertSame('TAX', $iata->getCode());
        self::assertCount(2, iterator_to_array($iata->getFlightIterator()));
    }

    #[Test]
    public function set_flight_resets_to_a_single_flight(): void
    {
        $iata = new Iata();
        $iata->addFlight($this->flight())->addFlight($this->flight());

        $iata->setFlight($this->flight());

        self::assertCount(1, iterator_to_array($iata->getFlightIterator()));
    }

    #[Test]
    public function flight_constructor_populates_its_legs(): void
    {
        $flight = $this->flight();

        self::assertSame('AB123', $flight->getNumber());
        self::assertSame('GRU', $flight->getFrom());
        self::assertSame('JFK', $flight->getTo());
        self::assertSame('2024-02-01', $flight->getDate());
    }

    #[Test]
    public function flight_accumulates_passengers(): void
    {
        $flight = $this->flight();
        $flight->addPassenger(new Passenger('Jane', 'jane@example.test', 'TK1'));
        $flight->addPassenger(new Passenger('John', 'john@example.test', 'TK2'));

        self::assertCount(2, $flight->getPassenger());
    }

    #[Test]
    public function set_passenger_resets_to_a_single_passenger(): void
    {
        $flight = $this->flight();
        $flight->addPassenger(new Passenger('Jane', 'jane@example.test', 'TK1'));

        $flight->setPassenger(new Passenger('John', 'john@example.test', 'TK2'));

        self::assertCount(1, $flight->getPassenger());
        self::assertSame('John', $flight->getPassenger()[0]->getName());
    }

    #[Test]
    public function passenger_carries_a_phone(): void
    {
        $passenger = new Passenger('Jane', 'jane@example.test', 'TK1');
        $passenger->setPhone(new Phone('11', '999998888', PhoneType::Home));

        self::assertSame('TK1', $passenger->getTicket());
        self::assertInstanceOf(Phone::class, $passenger->getPhone());
        self::assertSame(PhoneType::Home, $passenger->getPhone()->getType());
    }
}
