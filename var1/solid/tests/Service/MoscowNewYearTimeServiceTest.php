<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use RuntimeException;
use App\Config\IvanConfig;
use App\Service\MoscowNewYearTimeService;

class MoscowNewYearTimeServiceTest extends TestCase
{
    private MoscowNewYearTimeService $service;

    protected function setUp(): void
    {
        $config = new IvanConfig();
        $this->service = new MoscowNewYearTimeService($config);
    }

    public function testBaseYearReturnsMidnight(): void
    {
        $result = $this->service->calculate(2020);
        $this->assertEquals('00:00', $result);
    }

    public function testYearBeforeBaseYearThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Год должен быть не раньше 2020.');
        
        $this->service->calculate(2019);
    }

    public function testYear2021(): void
    {
        $result = $this->service->calculate(2021);
        $this->assertEquals('03:00', $result);
    }

    public function testYear2022(): void
    {
        $result = $this->service->calculate(2022);
        $this->assertEquals('22:00', $result);
    }

    public function testYear2023(): void
    {
        $result = $this->service->calculate(2023);
        $this->assertEquals('18:00', $result);
    }

    public function testYear2024(): void
    {
        $result = $this->service->calculate(2024);
        $this->assertEquals('14:00', $result);
    }

    public function testYear2025(): void
    {
        $result = $this->service->calculate(2025);
        $this->assertEquals('22:00', $result);
    }

    public function testYear2026(): void
    {
        $result = $this->service->calculate(2026);
        $this->assertEquals('18:00', $result);
    }

    public function testYear2027(): void
    {
        $result = $this->service->calculate(2027);
        $this->assertEquals('14:00', $result);
    }

    public function testMultipleYearsSequence(): void
    {
        $results = [];
        for ($year = 2020; $year <= 2027; $year++) {
            $results[$year] = $this->service->calculate($year);
        }

        $this->assertEquals('00:00', $results[2020]);
        $this->assertEquals('03:00', $results[2021]);
        $this->assertEquals('22:00', $results[2022]);
        $this->assertEquals('18:00', $results[2023]);
        $this->assertEquals('14:00', $results[2024]);
        $this->assertEquals('22:00', $results[2025]);
        $this->assertEquals('18:00', $results[2026]);
        $this->assertEquals('14:00', $results[2027]);
    }

    public function testResultFormat(): void
    {
        $result = $this->service->calculate(2021);
        
        // Проверяем формат HH:MM
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $result);
        
        // Проверяем, что часы в диапазоне 0-23
        [$hours, $minutes] = explode(':', $result);
        $this->assertGreaterThanOrEqual(0, (int)$hours);
        $this->assertLessThanOrEqual(23, (int)$hours);
        $this->assertGreaterThanOrEqual(0, (int)$minutes);
        $this->assertLessThan(60, (int)$minutes);
    }

    public function testWithCustomConfig(): void
    {
        $customConfig = new IvanConfig(
            baseYear: 2020,
            baseDate: '2020-01-01 00:00:00',
            departureHourOffset: 12,
            flightDurationHours: 2,
            restDurationHours: 6,
            timezoneShiftOnFlight: 3,
            startTimezoneIndex: 0
        );
        
        $customService = new MoscowNewYearTimeService($customConfig);
        $result = $customService->calculate(2020);
        
        $this->assertEquals('00:00', $result);
    }

    public function testYearZeroThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->calculate(0);
    }

    public function testNegativeYearThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->calculate(-1);
    }
}

