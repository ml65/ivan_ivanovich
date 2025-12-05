<?php

function indexToOffset(int $z): int
{
    $z = $z % 24;
    if ($z < 0) {
        $z += 24;
    }
    return $z <= 12 ? $z : $z - 24;
}

// t в часах от 01.01.2020 00:00 по Москве
function getZoneAndState(float $t): array
{
    if ($t < 12) {
        return [0, 'rest']; // до первого вылета в Москве
    }
    $tau   = $t - 12;             // часы с момента первого вылета
    $k     = intdiv((int)$tau, 8); // номер цикла (0,1,2,...)
    $phase = $tau % 8;            // фаза в цикле [0..7]

    if ($phase < 2) {
        // полёт, вылет из пояса z_dep
        $z_dep = (-3 * $k) % 24;
        if ($z_dep < 0) {
            $z_dep += 24;
        }
        return [$z_dep, 'flight'];
    } else {
        // отдых в поясе z_arr = z_dep - 3
        $z_arr = (-3 * ($k + 1)) % 24;
        if ($z_arr < 0) {
            $z_arr += 24;
        }
        return [$z_arr, 'rest'];
    }
}


function leapsUpto(int $n): int
{
    return intdiv($n, 4) - intdiv($n, 100) + intdiv($n, 400);
}

function daysFrom2020ToYearStart(int $year): int
{
    if ($year < 2020) {
        throw new InvalidArgumentException('year < 2020');
    }
    if ($year === 2020) {
        return 0;
    }
    $dy = $year - 2020;
    $L2019 = leapsUpto(2019);
    $leaps = leapsUpto($year - 1) - $L2019; // число високосных лет в [2020..year-1]
    return 365 * $dy + $leaps;
}


function getMoscowNewYearTimeNoLoop(int $year): string
{
    if ($year < 2020) {
        throw new InvalidArgumentException('year < 2020');
    }
    if ($year === 2020) {
        return '00:00';
    }

    $Hbase = daysFrom2020ToYearStart($year) * 24;
    $best  = null;

    for ($z = 0; $z < 24; $z++) {
        $offset = indexToOffset($z);
        $t0 = $Hbase - $offset; // момент, когда в поясе z наступает 01.01.Y 00:00

        if ($t0 < 12) {
            // до первого вылета — для Y > 2020 это не может случиться, но на всякий случай
            continue;
        }

        list($zoneAtT0, $stateAtT0) = getZoneAndState($t0);

        if ($stateAtT0 === 'rest') {
            if ($zoneAtT0 === $z) {
                $tCelebrate = $t0;
            } else {
                continue;
            }
        } else { // flight
            if ($zoneAtT0 !== $z) {
                continue;
            }
            // Новый год застал его в полёте — празднует после посадки
            $tau = $t0 - 12;
            $k   = intdiv((int)$tau, 8);
            $tStartFlight = 12 + 8 * $k;
            $tCelebrate   = $tStartFlight + 2;
        }

        if ($best === null || $tCelebrate < $best) {
            $best = $tCelebrate;
        }
    }

    if ($best === null) {
        throw new RuntimeException('Не удалось найти момент празднования.');
    }

    // Время в Москве — это просто best по модулю 24 часов
    $h = $best % 24;
    if ($h < 0) {
        $h += 24;
    }

    // Формат "HH:MM"
    return sprintf('%02d:00', $h);
}

