<?php

namespace Level23\Druid\Aggregations;

class LongMinAggregator extends LongSumAggregator
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'      => 'longMin',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }
}