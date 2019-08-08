<?php

namespace Level23\Druid\Aggregations;

class MaxAggregator extends SumAggregator
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'      => $this->type . 'Max',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }
}