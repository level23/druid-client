<?php

namespace Level23\Druid\Aggregations;

use InvalidArgumentException;
use Level23\Druid\Types\DataType;

class SumAggregator implements AggregatorInterface
{
    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var string
     */
    protected $metricName;

    /**
     * The type of field. This can either be "long", "float" or "double"
     *
     * @var string
     */
    protected $type;

    /**
     * constructor.
     *
     * @param string                        $metricName
     * @param string                        $outputName     When not given, we will use the same name as the metric.
     * @param \Level23\Druid\Types\DataType $type           The type of field. This can either be "long", "float" or
     *                                                      "double"
     */
    public function __construct(string $metricName, string $outputName = '', DataType $type = null)
    {
        $this->metricName = $metricName;
        $this->outputName = $outputName ?: $metricName;

        $type = strtolower($type ?: DataType::LONG());
        if (!in_array($type, ['long', 'float', 'double'])) {
            throw new InvalidArgumentException(
                'Incorrect type given: ' . $type . '. This can either be "long", "float" or "double"'
            );
        }

        $this->type = $type;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'      => $this->type . 'Sum',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }
}