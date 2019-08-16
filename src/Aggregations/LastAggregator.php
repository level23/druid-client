<?php

namespace Level23\Druid\Aggregations;

class LastAggregator extends SumAggregator
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'      => $this->type . 'Last',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }
}