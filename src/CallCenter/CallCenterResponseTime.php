<?php

namespace MilesNash\CallCenter;

use RuntimeException;

class CallCenterResponseTime implements CallCenterResponseTimeInterface
{
    /**
     * Response time metric
     *
     * @var string
     */
    private $metric;

    /**
     * Response time value
     *
     * @var int
     */
    private $value;

    /**
     * Constructor
     *
     * @throws RuntimeException If metric is not a valid CallCenterResponseTimeInterface time metric or value < 1
     */
    public function __construct(string $metric, int $value)
    {
        if (!in_array($metric, self::TIME_METRICS)) {
            throw new RuntimeException('Unknown time metric: ' . $metric);
        }

        if ($value < 1) {
            throw new RuntimeException('Value must be a positive integer');
        }

        $this->metric = $metric;
        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetric(): string
    {
        return $this->metric;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
