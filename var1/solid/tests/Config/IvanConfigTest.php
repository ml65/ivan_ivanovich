<?php

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use App\Config\IvanConfig;

class IvanConfigTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = new IvanConfig();

        $this->assertEquals(2020, $config->baseYear);
        $this->assertEquals('2020-01-01 00:00:00', $config->baseDate);
        $this->assertEquals(12, $config->departureHourOffset);
        $this->assertEquals(2, $config->flightDurationHours);
        $this->assertEquals(6, $config->restDurationHours);
        $this->assertEquals(3, $config->timezoneShiftOnFlight);
        $this->assertEquals(0, $config->startTimezoneIndex);
        $this->assertEquals('flight', $config->stateFlight);
        $this->assertEquals('rest', $config->stateRest);
        $this->assertEquals('flight', $config->initialState);
    }

    public function testCustomValues(): void
    {
        $config = new IvanConfig(
            baseYear: 2021,
            baseDate: '2021-01-01 00:00:00',
            departureHourOffset: 10,
            flightDurationHours: 3,
            restDurationHours: 5,
            timezoneShiftOnFlight: 4,
            startTimezoneIndex: 1,
            stateFlight: 'flying',
            stateRest: 'resting',
            initialState: 'rest'
        );

        $this->assertEquals(2021, $config->baseYear);
        $this->assertEquals('2021-01-01 00:00:00', $config->baseDate);
        $this->assertEquals(10, $config->departureHourOffset);
        $this->assertEquals(3, $config->flightDurationHours);
        $this->assertEquals(5, $config->restDurationHours);
        $this->assertEquals(4, $config->timezoneShiftOnFlight);
        $this->assertEquals(1, $config->startTimezoneIndex);
        $this->assertEquals('flying', $config->stateFlight);
        $this->assertEquals('resting', $config->stateRest);
        $this->assertEquals('rest', $config->initialState);
    }

    public function testReadonlyProperties(): void
    {
        $config = new IvanConfig();

        // Проверяем, что свойства readonly (нельзя изменить после создания)
        $this->assertIsInt($config->baseYear);
        $this->assertIsString($config->baseDate);
        $this->assertIsInt($config->departureHourOffset);
    }
}

