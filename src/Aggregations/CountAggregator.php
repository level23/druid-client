<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

class CountAggregator implements AggregatorInterface
{
    protected string $outputName;

    /**
     * CountAggregator constructor.
     *
     * @param string $outputName
     */
    public function __construct(string $outputName)
    {
        $this->outputName = $outputName;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'type' => 'count',
            'name' => $this->outputName,
        ];
    }
}