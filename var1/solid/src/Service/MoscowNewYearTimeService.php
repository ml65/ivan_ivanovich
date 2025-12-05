<?php

namespace App\Service;

use App\Config\IvanConfig;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;

/**
 * Сервис для расчета времени празднования Нового года в Москве.
 */
class MoscowNewYearTimeService
{
    public function __construct(
        private readonly IvanConfig $config
    ) {
    }

    /**
     * Возвращает строку "HH:MM" — сколько в Москве будет времени,
     * когда Иван Иванович отпразднует наступление заданного года.
     *
     * @param int $year Год >= baseYear
     * @return string Часы и минуты в Москве в формате "H:i"
     * @throws InvalidArgumentException
     */
    public function calculate(int $year): string
    {
        $this->validateYear($year);

        // Базовый год он встретил в Москве в обычное время.
        if ($year === $this->config->baseYear) {
            return '00:00';
        }

        // Базовая точка отсчёта: начало базового года "московского" времени.
        // Используем UTC просто как "сухую" шкалу времени без DST.
        $base = new DateTimeImmutable($this->config->baseDate, new DateTimeZone('UTC'));

        // В момент вылета проходит заданное количество часов от базовой точки.
        // Мировое (московское) время в часах от base:
        $H = $this->config->departureHourOffset;

        // Состояние Ивана:
        $state = $this->config->initialState;
        $hoursLeft = $this->config->flightDurationHours;
        $zoneIndex = $this->config->startTimezoneIndex;

        // Бесконечный цикл, пока не найдём момент празднования нужного года.
        while (true) {
            // Текущее московское время
            $moscow = $base->modify("+{$H} hours");

            // Текущее местное время Ивана
            $offset = $this->calculateTimezoneOffset($zoneIndex);
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
            if ($this->isNewYearMoment($localYear, $localMonth, $localDay, $localHour, $year)) {
                $celebration = $this->getCelebrationTime($state, $moscow, $base, $H, $hoursLeft);
                // Возвращаем московское время "часы:минуты"
                return $celebration->format('H:i');
            }

            // Переходим к следующему часу: уменьшаем оставшееся время фазы
            $hoursLeft--;

            if ($hoursLeft === 0) {
                if ($state === $this->config->stateFlight) {
                    // Завершили полёт — приземлились, сместились на заданное количество поясов на запад
                    $state = $this->config->stateRest;
                    $hoursLeft = $this->config->restDurationHours;
                    $zoneIndex = ($zoneIndex + 24 - $this->config->timezoneShiftOnFlight) % 24;
                } else {
                    // Завершили отдых — начинаем новый полёт
                    $state = $this->config->stateFlight;
                    $hoursLeft = $this->config->flightDurationHours;
                    // При взлёте часовой пояс тот же, смена будет по прилёте
                }
            }

            $H++;
        }
    }

    /**
     * Валидация года.
     */
    private function validateYear(int $year): void
    {
        if ($year < $this->config->baseYear) {
            throw new InvalidArgumentException('Год должен быть не раньше ' . $this->config->baseYear . '.');
        }
    }

    /**
     * Преобразует индекс часового пояса 0..23 в смещение от Москвы в часах [-12..12].
     */
    private function calculateTimezoneOffset(int $index): int
    {
        $index = $index % 24;
        if ($index < 0) {
            $index += 24;
        }
        // Диапазон [-12, 12], например:
        // 0 -> 0, 1 -> 1, ..., 12 -> 12, 13 -> -11, 14 -> -10, ..., 23 -> -1
        return ($index > 12) ? $index - 24 : $index;
    }

    /**
     * Проверяет момент наступления локального Нового года нужного года.
     */
    private function isNewYearMoment(int $localYear, int $localMonth, int $localDay, int $localHour, int $year): bool
    {
        return $localYear === $year &&
            $localMonth === 1 &&
            $localDay === 1 &&
            $localHour === 0;
    }

    /**
     * Получает время празднования в зависимости от состояния.
     */
    private function getCelebrationTime(
        string $state,
        DateTimeImmutable $moscow,
        DateTimeImmutable $base,
        int $H,
        int $hoursLeft
    ): DateTimeImmutable {
        if ($state === $this->config->stateRest) {
            // Празднует прямо сейчас
            return $moscow;
        } else {
            // В полёте — празднует после посадки
            $landingH = $H + $hoursLeft;
            return $base->modify("+{$landingH} hours");
        }
    }
}

