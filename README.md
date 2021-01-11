# Call center availability challenge

## Installation

To add the package in your project, include the following config in your composer.json:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/milesnash/call-center-availability"
        }
    ],
    "require": {
        "milesnash/call-center-availability": "dev-master"
    }
}
```

## Running tests

```bash
composer install
composer run test
```

Run with code coverage report ([xdebug](https://xdebug.org/docs/install) must be installed and enabled):
```bash
XDEBUG_MODE=coverage composer run test
```

## Usage

```php
<?php

use MilesNash\CallCenter\CallCenter;
use MilesNash\CallCenter\CallCenterConfig;
use MilesNash\CallCenter\CallCenterResponseTime;

$callCenter = new CallCenter(
    new CallCenterConfig(
        // Opening Hours
        [
            'monday' => ['09:00-18:01'],
            'tuesday' => ['09:00-18:01'],
            'wednesday' => ['09:00-18:01'],
            'thursday' => ['09:00-20:01'],
            'friday' => ['09:00-20:01'],
            'saturday' => ['09:00-12:31'],
            'sunday' => [],
        ],
        // Call center timezone
        new DateTimeZone("Europe/London"),
        // Minimum response time (i.e. the earliest time a call can be pre-booked from the current call center time)
        new CallCenterResponseTime(
            CallCenterResponseTime::TIME_METRIC_HOURS,
            2,
        ),
        // Maximum response time (i.e. the earliest time a call can be pre-booked from the current call center time)
        new CallCenterResponseTime(
            CallCenterResponseTime::TIME_METRIC_WORKING_DAYS,
            6,
        )
    )
);

$dt = new DateTimeImmutable("now");

$callCenter->isValidTime($dt); // true/false based on the call center config
```

NOTE: The opening hours parameter (parameter 1) to the CallCenterConfig constructor MUST conform the one defined in the [Spatie Opening Hours](https://github.com/spatie/opening-hours) package.

## Development notes

See [DEV_NOTES document](DEV_NOTES.md)
