<?php

namespace Level23\Druid\Aggregations;

class DoubleMaxAggregator extends LongSumAggregator
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'      => 'doubleMax',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }
}