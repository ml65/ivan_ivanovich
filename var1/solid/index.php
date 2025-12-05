<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\IvanConfig;
use App\Service\MoscowNewYearTimeService;

$config = new IvanConfig();
$service = new MoscowNewYearTimeService($config);

$start_time = microtime(true);

$year = 2020;
echo $year . ' ' . $service->calculate($year++) . "\n";
echo $year . ' ' . $service->calculate($year++) . "\n";
echo $year . ' ' . $service->calculate($year++) . "\n";
echo $year . ' ' . $service->calculate($year++) . "\n";
echo $year . ' ' . $service->calculate($year++) . "\n";
echo $year . ' ' . $service->calculate($year++) . "\n";
echo $year . ' ' . $service->calculate($year++) . "\n";
echo $year . ' ' . $service->calculate($year++) . "\n";

$end_time = microtime(true);
$execution_time = $end_time - $start_time;
echo "Время выполнения: " . round($execution_time * 1000000, 1) . " мкс\n";

