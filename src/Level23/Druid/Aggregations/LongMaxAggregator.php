<?php

namespace Level23\Druid\Aggregations;

class LongMaxAggregator extends LongSumAggregator
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'      => 'longMax',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }
}