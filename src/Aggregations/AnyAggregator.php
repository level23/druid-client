<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

use InvalidArgumentException;
use Level23\Druid\Types\DataType;

class AnyAggregator extends MethodAggregator
{
    /**
     * @var int|null
     */
    protected $maxStringBytes;

    /**
     * constructor.
     *
     * @param string   $metricName
     * @param string   $outputName                          When not given, we will use the same name as the metric.
     * @param string   $type                                The type of field. This can either be "long", "float" or
     *                                                      "double"
     * @param int|null $maxStringBytes                      optional, defaults to 1024
     */
    public function __construct(
        string $metricName,
        string $outputName = '',
        string $type = 'long',
        int $maxStringBytes = null
    ) {
        $type = strtolower($type);
        if (!in_array($type, ['long', 'float', 'double'])) {
            throw new InvalidArgumentException(
                'Incorrect type given: ' . $type . '. This can either be "long", "float" or "double"'
            );
        }

        $this->type           = $type;
        $this->metricName     = $metricName;
        $this->outputName     = $outputName ?: $metricName;
        $this->maxStringBytes = $maxStringBytes;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $response = [
            'type'      => $this->type . ucfirst($this->getMethod()),
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