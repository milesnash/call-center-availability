<?php

namespace MilesNash\CallCenter;

use DateTimeImmutable;

/**
 * CallCenterInterface
 *
 * Represents call center business logic
 */
interface CallCenterInterface
{
    /**
     * Returns true if the requested time satisfies all pre-booked call conditions
     *
     * @param DateTimeImmutable $requestedTime
     *
     * @return boolean
     */
    public function isValidTime(DateTimeImmutable $requestedTime): bool;

    /**
     * Get call center current time
     *
     * @return DateTimeImmutable
     */
    public function getCurrentTime(): DateTimeImmutable;

    /**
     * Override current time at call center
     *
     * @param DateTimeImmutable $currentTimeOverride
     *
     * @return CallCenterInterface
     */
    public function overrideCurrentTime(DateTimeImmutable $currentTimeOverride): CallCenterInterface;
}
