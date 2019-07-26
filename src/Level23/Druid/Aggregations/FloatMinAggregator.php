<?php

namespace Level23\Druid\Aggregations;

class FloatMinAggregator extends LongSumAggregator
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'      => 'floatMin',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }
}