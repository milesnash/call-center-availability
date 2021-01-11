<?php

namespace MilesNash\CallCenter\Test;

use RuntimeException;
use MilesNash\CallCenter\CallCenterResponseTime;
use PHPUnit\Framework\TestCase;

class CallCenterResponseTimeTest extends TestCase
{
    /**
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_it_throws_exception_when_constructed_with_invalid_metric()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown time metric: foo');

        new CallCenterResponseTime('foo', 0);
    }

    /**
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_it_throws_exception_when_constructed_with_invalid_value()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Value must be a positive integer');

        new CallCenterResponseTime('working_days', 0);
    }

    /**
     * @covers MilesNash\CallCenter\CallCenterResponseTime
     */
    public function test_it_constructs_with_valid_params()
    {
        $responseTime = new CallCenterResponseTime('hours', 1);

        $this->assertEquals('hours', $responseTime->getMetric());
        $this->assertEquals(1, $responseTime->getValue());
    }
}
