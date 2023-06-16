<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

use Level23\Druid\Types\DataType;

class AnyAggregator extends MethodAggregator
{
    protected ?int $maxStringBytes;

    /**
     * constructor.
     *
     * @param string          $metricName
     * @param string          $outputName                   When not given, we will use the same name as the metric.
     * @param string|DataType $type                         The type of field. This can either be "long", "float" or
     *                                                      "double"
     * @param int|null        $maxStringBytes               optional, defaults to 1024
     *
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        string $metricName,
        string $outputName = '',
        string|DataType $type = DataType::LONG,
        int $maxStringBytes = null
    ) {
        if (is_string($type)) {
            $type = DataType::from($type);
        }

        $this->type           = $type;
        $this->metricName     = $metricName;
        $this->outputName     = $outputName ?: $metricName;
        $this->maxStringBytes = $maxStringBytes;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array<string,string|int>
     */
    public function toArray(): array
    {
        $response = [
            'type'      => $this->type->value . ucfirst($this->getMethod()),
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];

        if ($this->type == DataType::STRING && $this->maxStringBytes !== null) {
            $response['maxStringBytes'] = $this->maxStringBytes;
        }

        return $response;
    }

    /**
     * Returns the method for the type aggregation
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return 'any';
    }
}