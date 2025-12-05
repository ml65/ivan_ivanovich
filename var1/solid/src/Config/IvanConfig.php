<?php

namespace App\Config;

/**
 * Параметры работы Ивана Ивановича.
 */
class IvanConfig
{
    // Базовый год, с которого начинается отсчёт
    public readonly int $baseYear;

    // Базовая дата отсчёта (начало базового года)
    public readonly string $baseDate;

    // Время вылета в часах от базовой даты (полдень 1 января)
    public readonly int $departureHourOffset;

    // Длительность полёта в часах
    public readonly int $flightDurationHours;

    // Длительность отдыха в часах
    public readonly int $restDurationHours;

    // Смещение часовых поясов при каждом перелёте (на запад, в часах)
    public readonly int $timezoneShiftOnFlight;

    // Начальный часовой пояс (0 = Москва)
    public readonly int $startTimezoneIndex;

    // Состояния Ивана Ивановича
    public readonly string $stateFlight;
    public readonly string $stateRest;

    // Начальное состояние
    public readonly string $initialState;

    public function __construct(
        int $baseYear = 2020,
        string $baseDate = '2020-01-01 00:00:00',
        int $departureHourOffset = 12,
        int $flightDurationHours = 2,
        int $restDurationHours = 6,
        int $timezoneShiftOnFlight = 3,
        int $startTimezoneIndex = 0,
        string $stateFlight = 'flight',
        string $stateRest = 'rest',
        string $initialState = 'flight'
    ) {
        $this->baseYear = $baseYear;
        $this->baseDate = $baseDate;
        $this->departureHourOffset = $departureHourOffset;
        $this->flightDurationHours = $flightDurationHours;
        $this->restDurationHours = $restDurationHours;
        $this->timezoneShiftOnFlight = $timezoneShiftOnFlight;
        $this->startTimezoneIndex = $startTimezoneIndex;
        $this->stateFlight = $stateFlight;
        $this->stateRest = $stateRest;
        $this->initialState = $initialState;
    }
}

