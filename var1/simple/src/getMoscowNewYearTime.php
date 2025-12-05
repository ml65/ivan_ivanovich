<?php

/**
 * Параметры работы Ивана Ивановича.
 */

// Базовый год, с которого начинается отсчёт
define('IVAN_BASE_YEAR', 2020);

// Базовая дата отсчёта (начало базового года)
define('IVAN_BASE_DATE', '2020-01-01 00:00:00');

// Время вылета в часах от базовой даты (полдень 1 января)
define('IVAN_DEPARTURE_HOUR_OFFSET', 12);

// Длительность полёта в часах
define('IVAN_FLIGHT_DURATION_HOURS', 2);

// Длительность отдыха в часах
define('IVAN_REST_DURATION_HOURS', 6);

// Смещение часовых поясов при каждом перелёте (на запад, в часах)
define('IVAN_TIMEZONE_SHIFT_ON_FLIGHT', 3);

// Начальный часовой пояс (0 = Москва)
define('IVAN_START_TIMEZONE_INDEX', 0);

// Состояния Ивана Ивановича
define('IVAN_STATE_FLIGHT', 'flight');
define('IVAN_STATE_REST', 'rest');

// Начальное состояние
define('IVAN_INITIAL_STATE', IVAN_STATE_FLIGHT);

/**
 * Возвращает строку "HH:MM" — сколько в Москве будет времени,
 * когда Иван Иванович отпразднует наступление заданного года.
 *
 * @param int $year Год >= IVAN_BASE_YEAR
 * @return string Часы и минуты в Москве в формате "H:i"
 * @throws InvalidArgumentException
 */
function getMoscowNewYearTime(int $year): string
{
    if ($year < IVAN_BASE_YEAR) {
        throw new InvalidArgumentException('Год должен быть не раньше ' . IVAN_BASE_YEAR . '.');
    }

    // Базовый год он встретил в Москве в обычное время.
    if ($year === IVAN_BASE_YEAR) {
        return '00:00';
    }

    // Базовая точка отсчёта: начало базового года "московского" времени.
    // Используем UTC просто как "сухую" шкалу времени без DST.
    $base = new DateTimeImmutable(IVAN_BASE_DATE, new DateTimeZone('UTC'));

    // В момент вылета проходит заданное количество часов от базовой точки.
    // Мировое (московское) время в часах от base:
    $H = IVAN_DEPARTURE_HOUR_OFFSET;

    // Состояние Ивана:
    $state = IVAN_INITIAL_STATE;
    $hoursLeft = IVAN_FLIGHT_DURATION_HOURS;
    $zoneIndex = IVAN_START_TIMEZONE_INDEX;

    /**
     * Преобразует индекс часового пояса 0..23 в смещение от Москвы в часах [-12..12].
     */
    $indexToOffset = static function (int $index): int {
        $index = $index % 24;
        if ($index < 0) {
            $index += 24;
        }
        // Диапазон [-12, 12], например:
        // 0 -> 0, 1 -> 1, ..., 12 -> 12, 13 -> -11, 14 -> -10, ..., 23 -> -1
        return ($index > 12) ? $index - 24 : $index;
    };

    // Бесконечный цикл, пока не найдём момент празднования нужного года.
    while (true) {
        // Текущее московское время
        $moscow = $base->modify("+{$H} hours");

        // Текущее местное время Ивана
        $offset = $indexToOffset($zoneIndex);
        $local = $moscow->modify(sprintf('%+d hours', $offset));

        $localYear  = (int)$local->format('Y');
        $localMonth = (int)$local->format('m');
        $localDay   = (int)$local->format('d');
        $localHour  = (int)$local->format('H');

        // Если уже проскочили нужный год — что-то пошло не так.
        if ($localYear > $year) {
            throw new RuntimeException('Логическая ошибка: Новый год не найден.');
        }

        // Проверяем момент наступления локального Нового года нужного года
        if (
            $localYear === $year &&
            $localMonth === 1 &&
            $localDay === 1 &&
            $localHour === 0
        ) {
            if ($state === IVAN_STATE_REST) {
                // Празднует прямо сейчас
                $celebration = $moscow;
            } else {
                // В полёте — празднует после посадки
                $landingH = $H + $hoursLeft;
                $celebration = $base->modify("+{$landingH} hours");
            }

            // Возвращаем московское время "часы:минуты"
            return $celebration->format('H:i');
        }

        // Переходим к следующему часу: уменьшаем оставшееся время фазы
        $hoursLeft--;

        if ($hoursLeft === 0) {
            if ($state === IVAN_STATE_FLIGHT) {
                // Завершили полёт — приземлились, сместились на заданное количество поясов на запад
                $state = IVAN_STATE_REST;
                $hoursLeft = IVAN_REST_DURATION_HOURS;
                $zoneIndex = ($zoneIndex + 24 - IVAN_TIMEZONE_SHIFT_ON_FLIGHT) % 24;
            } else {
                // Завершили отдых — начинаем новый полёт
                $state = IVAN_STATE_FLIGHT;
                $hoursLeft = IVAN_FLIGHT_DURATION_HOURS;
                // При взлёте часовой пояс тот же, смена будет по прилёте
            }
        }

        $H++;
    }
}

