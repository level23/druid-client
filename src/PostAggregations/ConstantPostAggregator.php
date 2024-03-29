<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class ConstantPostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected float $numericValue;

    /**
     * ConstantPostAggregator constructor.
     *
     * @param string $outputName
     * @param float  $numericValue
     */
    public function __construct(string $outputName, float $numericValue)
    {
        $this->outputName   = $outputName;
        $this->numericValue = $numericValue;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array<string,string|float>
     */
    public function toArray(): array
    {
        return [
            'type'  => 'constant',
            'name'  => $this->outputName,
            'value' => $this->numericValue,
        ];
    }
}