<?php

namespace Level23\Druid\Aggregations;

use InvalidArgumentException;
use Level23\Druid\Types\DataType;

abstract class MethodAggregator implements AggregatorInterface
{
    /**
     * The type of field. This can either be "long", "float" or "double"
     *
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var string
     */
    protected $metricName;

    /**
     * constructor.
     *
     * @param string          $metricName
     * @param string          $outputName                   When not given, we will use the same name as the metric.
     * @param DataType|string $type                         The type of field. This can either be "long", "float" or
     *                                                      "double"
     */
    public function __construct(string $metricName, string $outputName = '', $type = 'long')
    {
        $type = strtolower($type ?: DataType::LONG());

        if (!in_array($type, ['long', 'float', 'double'])) {
            throw new InvalidArgumentException(
                'Incorrect type given: ' . $type . '. This can either be "long", "float" or "double"'
            );
        }

        $this->type       = $type;
        $this->metricName = $metricName;
        $this->outputName = $outputName ?: $metricName;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'      => $this->type . ucfirst($this->getMethod()),
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }

    /**
     * Returns the method for the type aggregation
     *
     * @return string
     */
    protected abstract function getMethod(): string;
}