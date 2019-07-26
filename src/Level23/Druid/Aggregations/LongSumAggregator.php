<?php

namespace Level23\Druid\Aggregations;

class LongSumAggregator implements AggregatorInterface
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
     * constructor.
     *
     * @param string $metricName
     * @param string $outputName When not given, we will use the same name as the metric.
     */
    public function __construct(string $metricName, string $outputName = '')
    {
        $this->metricName = $metricName;
        $this->outputName = $outputName ?: $metricName;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'      => 'longSum',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }
}