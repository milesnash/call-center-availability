<?php

namespace MilesNash\CallCenter;

/**
 * Call center response time interface
 */
interface CallCenterResponseTimeInterface
{
    const TIME_METRIC_HOURS = 'hours';
    const TIME_METRIC_WORKING_DAYS = 'working_days';

    const TIME_METRICS = [
        self::TIME_METRIC_HOURS,
        self::TIME_METRIC_WORKING_DAYS,
    ];

    /**
     * The metric used for this response time. Matches one of the metric constants
     *
     * @return string
     */
    public function getMetric(): string;

    /**
     * The value used for this response time.
     *
     * @return int
     */
    public function getValue(): int;
}
