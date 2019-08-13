<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

class FirstAggregator extends SumAggregator
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'      => $this->type . 'First',
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