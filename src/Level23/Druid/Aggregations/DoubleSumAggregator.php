<?php

namespace Level23\Druid\Aggregations;

class DoubleSumAggregator extends LongSumAggregator
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'      => 'doubleSum',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }
}