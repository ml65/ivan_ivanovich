<?php

/**
 * Конфигурация параметров работы Ивана Ивановича
 */
final class IvanConstants
{
    public const int TIMEZONES_COUNT = 24;
    public const int BASE_YEAR = 2020;
    public const int FIRST_DEPARTURE_HOUR = 12;
    public const int CYCLE_DURATION_HOURS = 8; // 2 часа полет + 6 часов отдых
    public const int FLIGHT_DURATION_HOURS = 2;
    public const int REST_DURATION_HOURS = 6;
    public const int TIMEZONE_SHIFT_ON_FLIGHT = 3;
    public const int DAYS_IN_YEAR = 365;
    
    // Коэффициенты для расчета високосных лет
    public const int LEAP_YEAR_DIVISOR_4 = 4;
    public const int LEAP_YEAR_DIVISOR_100 = 100;
    public const int LEAP_YEAR_DIVISOR_400 = 400;
    
    // Состояния
    public const string STATE_REST = 'rest';
    public const string STATE_FLIGHT = 'flight';
}

/**
 * Нормализует число по модулю (приводит к диапазону [0, modulus))
 * 
 * @param int $value Значение для нормализации
 * @param int $modulus Модуль
 * @return int Нормализованное значение
 */
function normalizeModulo(int $value, int $modulus): int
{
    $result = $value % $modulus;
    if ($result < 0) {
        $result += $modulus;
    }
    return $result;
}

/**
 * Преобразует индекс часового пояса (0-23) в смещение от Москвы в часах (-12..12)
 * 
 * @param int $timezoneIndex Индекс часового пояса (0 = Москва)
 * @return int Смещение в часах от Москвы
 */
function convertTimezoneIndexToOffset(int $timezoneIndex): int
{
    $normalizedIndex = normalizeModulo($timezoneIndex, IvanConstants::TIMEZONES_COUNT);
    
    // Преобразование: 0-12 -> 0-12, 13-23 -> -11..-1
    return $normalizedIndex <= 12 
        ? $normalizedIndex 
        : $normalizedIndex - IvanConstants::TIMEZONES_COUNT;
}

/**
 * Определяет часовой пояс и состояние Ивана Ивановича в заданный момент времени
 * 
 * @param float $hoursFromBase Время в часах от 01.01.2020 00:00 по Москве
 * @return array{0: int, 1: string} Массив [индекс_часового_пояса, состояние]
 */
function getTimezoneAndStateAtTime(float $hoursFromBase): array
{
    if ($hoursFromBase < IvanConstants::FIRST_DEPARTURE_HOUR) {
        return [0, IvanConstants::STATE_REST]; // До первого вылета в Москве
    }
    
    $hoursSinceFirstDeparture = $hoursFromBase - IvanConstants::FIRST_DEPARTURE_HOUR;
    $cycleNumber = intdiv((int)$hoursSinceFirstDeparture, IvanConstants::CYCLE_DURATION_HOURS);
    $phaseInCycle = $hoursSinceFirstDeparture % IvanConstants::CYCLE_DURATION_HOURS;
    
    if ($phaseInCycle < IvanConstants::FLIGHT_DURATION_HOURS) {
        // Полёт, вылет из пояса departure_timezone
        $departureTimezone = normalizeModulo(
            -IvanConstants::TIMEZONE_SHIFT_ON_FLIGHT * $cycleNumber,
            IvanConstants::TIMEZONES_COUNT
        );
        return [$departureTimezone, IvanConstants::STATE_FLIGHT];
    } else {
        // Отдых в поясе arrival_timezone = departure_timezone - 3
        $arrivalTimezone = normalizeModulo(
            -IvanConstants::TIMEZONE_SHIFT_ON_FLIGHT * ($cycleNumber + 1),
            IvanConstants::TIMEZONES_COUNT
        );
        return [$arrivalTimezone, IvanConstants::STATE_REST];
    }
}

/**
 * Вычисляет количество високосных лет до указанного года (не включая)
 * 
 * @param int $year Год
 * @return int Количество високосных лет
 */
function countLeapYearsUpTo(int $year): int
{
    return intdiv($year, IvanConstants::LEAP_YEAR_DIVISOR_4)
         - intdiv($year, IvanConstants::LEAP_YEAR_DIVISOR_100)
         + intdiv($year, IvanConstants::LEAP_YEAR_DIVISOR_400);
}

/**
 * Вычисляет количество дней от начала 2020 года до начала указанного года
 * 
 * @param int $year Год (>= 2020)
 * @return int Количество дней
 * @throws InvalidArgumentException Если год < 2020
 */
function calculateDaysFromBaseYearToYearStart(int $year): int
{
    if ($year < IvanConstants::BASE_YEAR) {
        throw new InvalidArgumentException(
            sprintf('Год должен быть не меньше %d, получен: %d', IvanConstants::BASE_YEAR, $year)
        );
    }
    
    if ($year === IvanConstants::BASE_YEAR) {
        return 0;
    }
    
    $yearsDifference = $year - IvanConstants::BASE_YEAR;
    $leapYearsBefore2020 = countLeapYearsUpTo(IvanConstants::BASE_YEAR - 1);
    $leapYearsInRange = countLeapYearsUpTo($year - 1) - $leapYearsBefore2020;
    
    return IvanConstants::DAYS_IN_YEAR * $yearsDifference + $leapYearsInRange;
}

/**
 * Вычисляет время празднования, если Новый год застал в полете
 * 
 * @param float $newYearTime Время наступления Нового года
 * @return float Время празднования
 */
function calculateCelebrationTimeDuringFlight(float $newYearTime): float
{
    $hoursSinceFirstDeparture = $newYearTime - IvanConstants::FIRST_DEPARTURE_HOUR;
    $cycleNumber = intdiv((int)$hoursSinceFirstDeparture, IvanConstants::CYCLE_DURATION_HOURS);
    $flightStartTime = IvanConstants::FIRST_DEPARTURE_HOUR + IvanConstants::CYCLE_DURATION_HOURS * $cycleNumber;
    
    return $flightStartTime + IvanConstants::FLIGHT_DURATION_HOURS; // Время посадки
}

/**
 * Находит лучшее время празднования среди всех часовых поясов
 * 
 * @param int $targetYear Целевой год
 * @return float Время празднования в часах от базовой даты
 * @throws RuntimeException Если не удалось найти момент празднования
 */
function findBestCelebrationTime(int $targetYear): float
{
    $baseHours = calculateDaysFromBaseYearToYearStart($targetYear) * IvanConstants::TIMEZONES_COUNT;
    $bestTime = null;
    
    for ($timezoneIndex = 0; $timezoneIndex < IvanConstants::TIMEZONES_COUNT; $timezoneIndex++) {
        $timezoneOffset = convertTimezoneIndexToOffset($timezoneIndex);
        $newYearTimeInTimezone = $baseHours - $timezoneOffset;
        
        if ($newYearTimeInTimezone < IvanConstants::FIRST_DEPARTURE_HOUR) {
            continue; // До первого вылета
        }
        
        [$zoneAtNewYear, $stateAtNewYear] = getTimezoneAndStateAtTime($newYearTimeInTimezone);
        
        $celebrationTime = match ($stateAtNewYear) {
            IvanConstants::STATE_REST => $zoneAtNewYear === $timezoneIndex 
                ? $newYearTimeInTimezone 
                : null,
            IvanConstants::STATE_FLIGHT => $zoneAtNewYear === $timezoneIndex
                ? calculateCelebrationTimeDuringFlight($newYearTimeInTimezone)
                : null,
            default => throw new RuntimeException("Неизвестное состояние: $stateAtNewYear")
        };
        
        if ($celebrationTime !== null && ($bestTime === null || $celebrationTime < $bestTime)) {
            $bestTime = $celebrationTime;
        }
    }
    
    if ($bestTime === null) {
        throw new RuntimeException('Не удалось найти момент празднования.');
    }
    
    return $bestTime;
}

/**
 * Форматирует время в формате "HH:MM"
 * 
 * @param float $hours Время в часах
 * @return string Время в формате "HH:MM"
 */
function formatTimeAsHoursMinutes(float $hours): string
{
    $normalizedHours = normalizeModulo((int)$hours, IvanConstants::TIMEZONES_COUNT);
    return sprintf('%02d:00', $normalizedHours);
}

/**
 * Возвращает строку "HH:MM" — сколько в Москве будет времени,
 * когда Иван Иванович отпразднует наступление заданного года.
 *
 * @param int $year Год >= 2020
 * @return string Часы и минуты в Москве в формате "HH:MM"
 * @throws InvalidArgumentException Если год < 2020
 * @throws RuntimeException Если не удалось найти момент празднования
 */
function getMoscowNewYearTimeNoLoop(int $year): string
{
    if ($year < IvanConstants::BASE_YEAR) {
        throw new InvalidArgumentException(
            sprintf('Год должен быть не меньше %d, получен: %d', IvanConstants::BASE_YEAR, $year)
        );
    }
    
    if ($year === IvanConstants::BASE_YEAR) {
        return '00:00';
    }
    
    $bestCelebrationTime = findBestCelebrationTime($year);
    return formatTimeAsHoursMinutes($bestCelebrationTime);
}
