<?php

require_once __DIR__ . '/src/getMoscowNewYearTimeNoLoop.php';

$start_time = microtime(true);

$year = 2020;
echo $year . ' ' . getMoscowNewYearTimeNoLoop($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTimeNoLoop($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTimeNoLoop($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTimeNoLoop($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTimeNoLoop($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTimeNoLoop($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTimeNoLoop($year++) . "\n";
echo $year . ' ' . getMoscowNewYearTimeNoLoop($year++) . "\n";

$end_time = microtime(true);
$execution_time = $end_time - $start_time;
echo "Время выполнения: " . round($execution_time * 1000000, 1) . " мкс\n";
