<?php

require_once __DIR__ . '/src/getMoscowNewYearTime.php';

$start_time = microtime(true);

$year = 2020;
echo $year . ' ' . getMoscowNewYearTime($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTime($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTime($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTime($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTime($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTime($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTime($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTime($year++) . "\n";

$end_time = microtime(true);
$execution_time = $end_time - $start_time;
echo "Время выполнения: " . round($execution_time * 1000000, 1) . " мкс\n";

