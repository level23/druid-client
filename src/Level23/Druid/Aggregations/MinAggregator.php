<?php

namespace Level23\Druid\Aggregations;

class MinAggregator extends SumAggregator
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'      => $this->type . 'Min',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];
    }

    /**
     * Return how this aggregation will be outputted in the query results.
     *
     * @return string
     */
    public function getOutputName(): string
    {
        return $this->outputName;
    }
}