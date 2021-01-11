<?php

namespace MilesNash\CallCenter;

use DateTimeZone;

/**
 * Call Center Config Interface
 *
 * Represents the configurable parameters of a call center
 */
interface CallCenterConfigInterface
{
    const MONDAY = 'monday';
    const TUESDAY = 'tuesday';
    const WEDNESDAY = 'wednesday';
    const THURSDAY = 'thursday';
    const FRIDAY = 'friday';
    const SATURDAY = 'saturday';
    const SUNDAY = 'sunday';

    /**
     * Order corresponding to the DateTime::format's "w" format
     */
    const NUMERIC_DAYS_OF_WEEK = [
        self::SUNDAY,
        self::MONDAY,
        self::TUESDAY,
        self::WEDNESDAY,
        self::THURSDAY,
        self::FRIDAY,
        self::SATURDAY,
    ];
        
    /**
     * Get call center opening hours
     *
     * @return array
     */
    public function getOpeningHours(): array;

    /**
     * Get call center timezone
     *
     * @return DateTimeZone
     */
    public function getTimeZone(): DateTimeZone;

    /**
     * Get call center min response time
     *
     * @return CallCenterResponseTimeInterface
     */
    public function getMinResponseTime(): CallCenterResponseTimeInterface;

    /**
     * Get call center max response time
     *
     * @return CallCenterResponseTimeInterface
     */
    public function getMaxResponseTime(): CallCenterResponseTimeInterface;
}
