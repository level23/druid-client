<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class QuantilePostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected PostAggregatorInterface $dimension;

    protected float $fraction;

    /**
     * QuantilePostAggregator constructor.
     *
     * @param PostAggregatorInterface $dimension    Post aggregator that refers to a DoublesSketch (fieldAccess or
     *                                              another post aggregator)
     * @param string                  $outputName   The name as it will be used in our result.
     * @param float                   $fraction     fractional position in the hypothetical sorted stream, number from 0
     *                                              to 1 inclusive
     */
    public function __construct(PostAggregatorInterface $dimension, string $outputName, float $fraction)
    {
        $this->outputName = $outputName;
        $this->dimension  = $dimension;
        $this->fraction   = $fraction;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array<string,string|array<string,string|array<mixed>>|float>
     */
    public function toArray(): array
    {
        return [
            'type'     => 'quantilesDoublesSketchToQuantile',
            'name'     => $this->outputName,
            'field'    => $this->dimension->toArray(),
            'fraction' => $this->fraction,
        ];
    }
}