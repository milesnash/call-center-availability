<?php

namespace MilesNash\CallCenter;

use DateTimeZone;

/**
 * CallCenterConfig
 */
class CallCenterConfig implements CallCenterConfigInterface
{
    /**
     * Call center opening hours
     *
     * @var array
     */
    private $openingHours;

    /**
     * Call center timezone
     *
     * @var DateTimeZone
     */
    private $timeZone;

    /**
     * Call center min response time
     *
     * @var CallCenterResponseTimeInterface
     */
    private $minResponseTime;

    /**
     * Call center max response time
     *
     * @var CallCenterResponseTimeInterface
     */
    private $maxResponseTime;

    public function __construct(
        array $openingHours,
        DateTimeZone $timeZone,
        CallCenterResponseTimeInterface $minResponseTime,
        CallCenterResponseTimeInterface $maxResponseTime
    ) {
        $this->openingHours = $openingHours;
        $this->timeZone = $timeZone;
        $this->minResponseTime = $minResponseTime;
        $this->maxResponseTime = $maxResponseTime;
    }

    /**
     * {@inheritDoc}
     */
    public function getOpeningHours(): array
    {
        return $this->openingHours;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeZone(): DateTimeZone
    {
        return $this->timeZone;
    }

    /**
     * {@inheritDoc}
     */
    public function getMinResponseTime(): CallCenterResponseTimeInterface
    {
        return $this->minResponseTime;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxResponseTime(): CallCenterResponseTimeInterface
    {
        return $this->maxResponseTime;
    }
}
