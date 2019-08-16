<?php

namespace Level23\Druid\Aggregations;

class MinAggregator extends SumAggregator
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'      => $this->type . 'Min',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }
}