# Проект расчета времени празднования Нового года

Проект для расчета времени празднования Нового года в Москве для путешественника Ивана Ивановича, который постоянно перемещается между часовыми поясами.

## Описание

Иван Иванович путешествует по миру по определенному алгоритму:
- Начинает с базового года (2020) в Москве
- Чередует полеты (2 часа) и отдых (6 часов)
- При каждом перелете смещается на 3 часовых пояса на запад
- Празднует Новый год в момент наступления полуночи в его текущем часовом поясе
- Если Новый год наступает во время полета, празднует после посадки

Проект рассчитывает, в какое московское время Иван Иванович отпразднует наступление заданного года.

## Особенности проекта


## Структура проекта

```
solid/
├── src/
│   ├── Config/
│   │   └── IvanConfig.php              # App\Config\IvanConfig
│   └── Service/
│       └── MoscowNewYearTimeService.php  # App\Service\MoscowNewYearTimeService
├── tests/
│   ├── Config/
│   │   └── IvanConfigTest.php         # Tests\Config\IvanConfigTest
│   └── Service/
│       └── MoscowNewYearTimeServiceTest.php  # Tests\Service\MoscowNewYearTimeServiceTest
├── vendor/                            # Зависимости Composer (автозагрузка)
├── index.php                          # Пример использования
├── composer.json                      # Зависимости и PSR-4 автозагрузка
├── phpunit.xml                        # Конфигурация PHPUnit
└── README.md                          # Документация
```

## Требования

- PHP >= 8.1
- Composer (для установки зависимостей)

## Установка

1. Клонируйте репозиторий или скопируйте проект

2. Установите зависимости:
```bash
composer install
```

## Использование

### Базовое использование

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\IvanConfig;
use App\Service\MoscowNewYearTimeService;

// Создаем конфигурацию с параметрами по умолчанию
$config = new IvanConfig();

// Создаем сервис
$service = new MoscowNewYearTimeService($config);

// Рассчитываем время празднования для 2021 года
$time = $service->calculate(2021);
echo "Время празднования: $time\n"; // Выведет: 03:00
```

### Использование с пользовательской конфигурацией

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\IvanConfig;
use App\Service\MoscowNewYearTimeService;

// Создаем конфигурацию с пользовательскими параметрами
$config = new IvanConfig(
    baseYear: 2020,
    baseDate: '2020-01-01 00:00:00',
    departureHourOffset: 12,
    flightDurationHours: 2,
    restDurationHours: 6,
    timezoneShiftOnFlight: 3,
    startTimezoneIndex: 0
);

$service = new MoscowNewYearTimeService($config);
$time = $service->calculate(2025);
```

### Запуск примера

```bash
php index.php
```

Вывод:
```
2020 00:00
2021 03:00
2022 22:00
2023 18:00
2024 14:00
2025 22:00
2026 18:00
2027 14:00
Время выполнения: 816199.1 мкс
```

## Тестирование

Проект включает полный набор юнит-тестов, покрывающих все основные сценарии.

### Запуск всех тестов

```bash
./vendor/bin/phpunit
```

или

```bash
composer test
```

(если команда `test` добавлена в `composer.json`)


## Покрытие тестами

Тесты покрывают:

### IvanConfig
- ✅ Значения по умолчанию
- ✅ Пользовательские значения
- ✅ Readonly свойства

### MoscowNewYearTimeService
- ✅ Базовый год (возвращает 00:00)
- ✅ Валидация года (исключения для невалидных годов)
- ✅ Расчет для различных годов (2021-2027)
- ✅ Последовательность нескольких лет
- ✅ Формат результата
- ✅ Работа с пользовательской конфигурацией

## Параметры конфигурации

Класс `IvanConfig` поддерживает следующие параметры:

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `baseYear` | int | 2020 | Базовый год отсчета |
| `baseDate` | string | '2020-01-01 00:00:00' | Базовая дата отсчета |
| `departureHourOffset` | int | 12 | Время вылета в часах от базовой даты |
| `flightDurationHours` | int | 2 | Длительность полета в часах |
| `restDurationHours` | int | 6 | Длительность отдыха в часах |
| `timezoneShiftOnFlight` | int | 3 | Смещение часовых поясов при перелете |
| `startTimezoneIndex` | int | 0 | Начальный часовой пояс (0 = Москва) |
| `stateFlight` | string | 'flight' | Состояние "полет" |
| `stateRest` | string | 'rest' | Состояние "отдых" |
| `initialState` | string | 'flight' | Начальное состояние |

## Обработка ошибок

Сервис выбрасывает исключения в следующих случаях:

- `InvalidArgumentException` - если передан год меньше базового года
- `RuntimeException` - если произошла логическая ошибка при расчете

