<?php

namespace MilesNash\CallCenter\Test;

use \date_default_timezone_set;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use MilesNash\CallCenter\CallCenter;
use MilesNash\CallCenter\CallCenterConfig;
use MilesNash\CallCenter\CallCenterResponseTime;
use PHPUnit\Framework\TestCase;

class CallCenterTest extends TestCase
{
    protected function setUp(): void
    {
        date_default_timezone_set('UTC');

        $this->minResponseTime = new CallCenterResponseTime(
            CallCenterResponseTime::TIME_METRIC_HOURS,
            2,
        );

        $this->maxResponseTime = new CallCenterResponseTime(
            CallCenterResponseTime::TIME_METRIC_WORKING_DAYS,
            6,
        );

        $this->callCenterConfig = new CallCenterConfig(
            [
                'monday' => ['09:00-18:01'],
                'tuesday' => ['09:00-18:01'],
                'wednesday' => ['09:00-18:01'],
                'thursday' => ['09:00-20:01'],
                'friday' => ['09:00-20:01'],
                'saturday' => ['09:00-12:31'],
                'sunday' => [],
            ],
            new DateTimeZone("UTC"),
            $this->minResponseTime,
            $this->maxResponseTime,
        );
    }

    /**
     * @covers MilesNash\CallCenter\CallCenter
     * @covers MilesNash\CallCenter\CallCenterConfig
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_it_returns_the_call_center_time()
    {
        $callCenter = new CallCenter(
            $this->callCenterConfig
        );

        $callCenterTime = $callCenter->getCurrentTime();

        $this->assertEquals(
            new DateTimeZone("UTC"),
            $callCenterTime->getTimezone()
        );
    }

    /**
     * @covers MilesNash\CallCenter\CallCenter
     * @covers MilesNash\CallCenter\CallCenterConfig
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_it_returns_false_when_callback_time_out_of_hours()
    {
        $callCenter = new CallCenter(
            $this->callCenterConfig
        );

        // A fixed Monday to ensure min/max response time isn't a factor
        $callCenterTime = new DateTimeImmutable(
            "2020-11-23T12:00"
        );

        $callCenter->overrideCurrentTime($callCenterTime);

        $outOfHourTimeModifiers = [
            // too early
            "monday 08:59",
            "tuesday 08:59",
            "wednesday 08:59",
            "thursday 08:59",
            "friday 08:59",
            "saturday 08:59",
            // too late
            "monday 18:01",
            "tuesday 18:01",
            "wednesday 18:01",
            "thursday 20:01",
            "friday 20:01",
            "saturday 12:31",
            // closed all day
            "sunday 09:00",
            "sunday 12:30",
        ];

        foreach ($outOfHourTimeModifiers as $outOfHourTimeModifier) {
            $this->assertFalse($callCenter->isValidTime($callCenterTime->modify($outOfHourTimeModifier)));
        }
    }

    /**
     * @covers MilesNash\CallCenter\CallCenter
     * @covers MilesNash\CallCenter\CallCenterConfig
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_it_returns_true_when_callback_time_in_hours()
    {
        $callCenter = new CallCenter(
            $this->callCenterConfig
        );

        // A fixed Sunday to ensure min/max response time isn't a factor
        $callCenterTime = new DateTimeImmutable(
            "2020-11-22T12:00"
        );

        $callCenter->overrideCurrentTime($callCenterTime);

        $inHourTimeModifiers = [
            // first open
            "monday 09:00",
            "tuesday 09:00",
            "wednesday 09:00",
            "thursday 09:00",
            "friday 09:00",
            "saturday 09:00",
            // last open
            "monday 18:00",
            "tuesday 18:00",
            "wednesday 18:00",
            "thursday 20:00",
            "friday 20:00",
            "saturday 12:30",
        ];

        foreach ($inHourTimeModifiers as $inHourTimeModifier) {
            $this->assertTrue($callCenter->isValidTime($callCenterTime->modify($inHourTimeModifier)));
        }
    }

    /**
     * @covers MilesNash\CallCenter\CallCenter
     * @covers MilesNash\CallCenter\CallCenterConfig
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_it_returns_false_when_requested_time_before_min_response_time()
    {
        $callCenter = new CallCenter(
            $this->callCenterConfig
        );

        $callCenter->overrideCurrentTime(new DateTimeImmutable(
            "2020-11-23T09:00"
        ));

        $requestedTime = new DateTimeImmutable(
            "2020-11-23T10:59"
        );

        $this->assertFalse($callCenter->isValidTime($requestedTime));
    }

    /**
     * @covers MilesNash\CallCenter\CallCenter
     * @covers MilesNash\CallCenter\CallCenterConfig
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_it_returns_true_when_requested_time_on_min_response_time()
    {
        $callCenter = new CallCenter(
            $this->callCenterConfig
        );

        $callCenter->overrideCurrentTime(new DateTimeImmutable(
            "2020-11-23T09:00"
        ));

        $requestedTime = new DateTimeImmutable(
            "2020-11-23T11:00"
        );

        $this->assertTrue($callCenter->isValidTime($requestedTime));
    }

    /**
     * @covers MilesNash\CallCenter\CallCenter
     * @covers MilesNash\CallCenter\CallCenterConfig
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_it_returns_false_when_requested_time_after_max_response_time()
    {
        $callCenter = new CallCenter(
            $this->callCenterConfig
        );

        $callCenter->overrideCurrentTime(new DateTimeImmutable(
            "2020-11-23T09:00"
        ));

        $requestedTime = new DateTimeImmutable(
            "2020-12-01T09:00"
        );

        $this->assertFalse($callCenter->isValidTime($requestedTime));
    }

    /**
     * @covers MilesNash\CallCenter\CallCenter
     * @covers MilesNash\CallCenter\CallCenterConfig
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_it_returns_true_when_requested_time_on_max_response_time()
    {
        $callCenter = new CallCenter(
            $this->callCenterConfig
        );

        $callCenter->overrideCurrentTime(new DateTimeImmutable(
            "2020-11-23T09:00"
        ));

        $requestedTime = new DateTimeImmutable(
            "2020-11-30T18:00"
        );

        $this->assertTrue($callCenter->isValidTime($requestedTime));
    }

    /**
     * @covers MilesNash\CallCenter\CallCenter
     * @covers MilesNash\CallCenter\CallCenterConfig
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_logic_works_across_timezones()
    {
        $callCenter = new CallCenter(
            new CallCenterConfig(
                [
                    'monday' => ['09:00-18:01'],
                ],
                new DateTimeZone("+0200"),
                new CallCenterResponseTime(
                    CallCenterResponseTime::TIME_METRIC_HOURS,
                    2,
                ),
                new CallCenterResponseTime(
                    CallCenterResponseTime::TIME_METRIC_WORKING_DAYS,
                    6,
                ),
            )
        );

        $callCenter->overrideCurrentTime(new DateTimeImmutable(
            "2020-11-23T09:00",
            new DateTimeZone("+0200")
        ));

        // Amounts to 11:00 in the call center TZ, just at the two hour min response time
        $inHourTime = new DateTimeImmutable(
            "2020-11-23T10:00",
            new DateTimeZone("+0100")
        );

        $this->assertTrue($callCenter->isValidTime($inHourTime));

        $outOfHourTime = $inHourTime->sub(new DateInterval("PT1M"));

        $this->assertFalse($callCenter->isValidTime($outOfHourTime));
    }
}
