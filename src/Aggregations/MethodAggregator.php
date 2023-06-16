<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

use InvalidArgumentException;
use Level23\Druid\Types\DataType;

abstract class MethodAggregator implements AggregatorInterface
{
    protected DataType $type;

    protected string $outputName;

    protected string $metricName;

    /**
     * constructor.
     *
     * @param string          $metricName
     * @param string          $outputName                   When not given, we will use the same name as the metric.
     * @param string|DataType $type                         The type of field. This can either be "long", "float" or
     *                                                      "double"
     */
    public function __construct(string $metricName, string $outputName = '', string|DataType $type = DataType::LONG)
    {
        if (is_string($type)) {
            $type = DataType::from(strtolower($type));
        }
        if (!in_array($type, [DataType::LONG, DataType::FLOAT, DataType::DOUBLE])) {
            throw new InvalidArgumentException(
                'Incorrect type given: ' . $type->value . '. This can either be "long", "float" or "double"'
            );
        }

        $this->type       = $type;
        $this->metricName = $metricName;
        $this->outputName = $outputName ?: $metricName;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'type'      => $this->type->value . ucfirst($this->getMethod()),
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