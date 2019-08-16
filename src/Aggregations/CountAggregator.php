<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

class CountAggregator implements AggregatorInterface
{
    /**
     * @var string
     */
    protected $outputName;

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
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => 'count',
            'name' => $this->outputName,
        ];
    }
}