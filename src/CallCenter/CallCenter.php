<?php

namespace MilesNash\CallCenter;

use DateInterval;
use DateTimeImmutable;
use RuntimeException;
use Spatie\OpeningHours\OpeningHours;

class CallCenter implements CallCenterInterface
{
    /**
     * Call center config object
     *
     * @var CallCenterConfigInterface
     */
    private $callCenterConfig;

    /**
     * Opening hours logic handler
     *
     * @var Spatie\OpeningHours\OpeningHours
     */
    private $openingHoursHandler;

    /**
     * Overridden current time
     *
     * @var DateTimeImmutable
     */
    private $currentTimeOverride;

    public function __construct(
        CallCenterConfigInterface $callCenterConfig
    ) {
        $this->callCenterConfig = $callCenterConfig;

        $this->openingHoursHandler = OpeningHours::create(
            $this->callCenterConfig->getOpeningHours(),
            $this->callCenterConfig->getTimeZone()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentTime(): DateTimeImmutable
    {
        return $this->currentTimeOverride ?? new DateTimeImmutable(
            "now",
            $this->callCenterConfig->getTimeZone()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function overrideCurrentTime(DateTimeImmutable $currentTimeOverride): CallCenterInterface
    {
        $this->currentTimeOverride = $currentTimeOverride;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isValidTime(DateTimeImmutable $requestedTime): bool
    {
        $withinOpeningHours = $this->openingHoursHandler->isOpenAt($requestedTime);

        if (!$withinOpeningHours) {
            return false;
        }

        $requestedTimeAtCallCenter = $requestedTime->setTimezone(
            $this->callCenterConfig->getTimeZone()
        );

        return
            $this->onOrAfterMinResponseTime($requestedTimeAtCallCenter)
            &&
            $this->onOrBeforeMaxResponseTime($requestedTimeAtCallCenter)
        ;
    }

    private function onOrAfterMinResponseTime(DateTimeImmutable $requestedTime): bool
    {
        $minResponseTime = $this->calculateRelativeResponseDateTime(
            $this->callCenterConfig->getMinResponseTime()
        );

        return
            $this->transformToCorrectPrecision(
                $requestedTime,
                $this->callCenterConfig->getMinResponseTime()
            ) >=
            $this->transformToCorrectPrecision(
                $minResponseTime,
                $this->callCenterConfig->getMinResponseTime()
            );
    }

    private function onOrBeforeMaxResponseTime(DateTimeImmutable $requestedTime): bool
    {
        $maxResponseTime = $this->calculateRelativeResponseDateTime(
            $this->callCenterConfig->getMaxResponseTime()
        );

        return
            $this->transformToCorrectPrecision(
                $requestedTime,
                $this->callCenterConfig->getMaxResponseTime()
            ) <=
            $this->transformToCorrectPrecision(
                $maxResponseTime,
                $this->callCenterConfig->getMaxResponseTime()
            );
    }

    private function transformToCorrectPrecision(
        DateTimeImmutable $dt,
        CallCenterResponseTimeInterface $responseTime
    ): DateTimeImmutable {
        $precisionDt = null;

        switch ($responseTime->getMetric()) {
            case CallCenterResponseTimeInterface::TIME_METRIC_HOURS:
                $precisionDt = new DateTimeImmutable($dt->format('Y-m-d\TH:i'));
                break;

            case CallCenterResponseTimeInterface::TIME_METRIC_WORKING_DAYS:
                $precisionDt = new DateTimeImmutable($dt->format('Y-m-d'));
                break;
            
            // Should be unreachable as a valid metric must be configured
            // @codeCoverageIgnoreStart
            default:
                throw new RuntimeException('Invalid metric encountered: ' . $responseTime->getMetric());
                break;
            // @codeCoverageIgnoreEnd
        }

        return $precisionDt;
    }

    private function calculateRelativeResponseDateTime(
        CallCenterResponseTimeInterface $responseTime
    ): DateTimeImmutable {
        $targetDateTime = null;

        switch ($responseTime->getMetric()) {
            case CallCenterResponseTimeInterface::TIME_METRIC_HOURS:
                $targetDateTime = $this->buildDateTimeXHoursFromNow($responseTime->getValue());
                break;

            case CallCenterResponseTimeInterface::TIME_METRIC_WORKING_DAYS:
                $targetDateTime = $this->buildDateTimeXWorkingDaysFromNow($responseTime->getValue());
                break;
            
            // Should be unreachable as a valid response time must be configured
            // @codeCoverageIgnoreStart
            default:
                throw new RuntimeException('Invalid response time encountered: ' . $responseTime->getMetric());
                break;
            // @codeCoverageIgnoreEnd
        }

        return $targetDateTime;
    }

    private function buildDateTimeXHoursFromNow(int $hours): DateTimeImmutable
    {
        return $this->getCurrentTime()->add(new DateInterval('PT' . $hours . 'H'));
    }

    private function buildDateTimeXWorkingDaysFromNow(int $workingDays): DateTimeImmutable
    {
        $dt = $this->getCurrentTime();

        $callCenterWorkingDayInts = $this->getCallCenterWorkingDayInts();

        if (empty($callCenterWorkingDayInts)) {
            // Call center is never open!
            // Should be unreachable as $openingHoursHandler will always return false first
            // @codeCoverageIgnoreStart
            throw new RuntimeException('No call center working days found in ' . __METHOD__);
            // @codeCoverageIgnoreEnd
        }

        while ($workingDays > 0) {
            $dt = $dt->add(new DateInterval("P1D"));
            $workingDayInt = $dt->format("w");

            if (in_array($workingDayInt, $callCenterWorkingDayInts)) {
                --$workingDays;
            }
        }

        return $dt;
    }

    private function getCallCenterWorkingDayInts(): array
    {
        $workingDays = [];

        foreach ($this->callCenterConfig->getOpeningHours() as $day => $hours) {
            $formattedDay = strtolower($day);
            if (!empty($hours) && in_array($formattedDay, CallCenterConfigInterface::NUMERIC_DAYS_OF_WEEK)) {
                $workingDays[] = array_search($formattedDay, CallCenterConfigInterface::NUMERIC_DAYS_OF_WEEK);
            }
        }

        return $workingDays;
    }
}
