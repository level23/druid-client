<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

use Level23\Druid\Types\DataType;

class LastAggregator extends MethodAggregator
{
    /**
     * constructor.
     *
     * @param string          $metricName
     * @param string          $outputName When not given, we will use the same name as the metric.
     * @param string|DataType $type       The type of field. This can either be "long", "float", "double" or "string"
     *
     */
    public function __construct(string $metricName, string $outputName = '', string|DataType $type = DataType::LONG)
    {
        $this->type       = is_string($type) ? DataType::from(strtolower($type)) : $type;
        $this->metricName = $metricName;
        $this->outputName = $outputName ?: $metricName;
    }

    /**
     * Returns the method for the type aggregation
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return 'last';
    }
}