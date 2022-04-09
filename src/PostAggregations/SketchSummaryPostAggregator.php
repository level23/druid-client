<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class SketchSummaryPostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected PostAggregatorInterface $dimension;

    /**
     * QuantilePostAggregator constructor.
     *
     * @param PostAggregatorInterface $dimension    Post aggregator that refers to a DoublesSketch (fieldAccess or
     *                                              another post aggregator)
     * @param string                  $outputName   The name as it will be used in our result.
     */
    public function __construct(PostAggregatorInterface $dimension, string $outputName)
    {
        $this->outputName = $outputName;
        $this->dimension  = $dimension;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'  => 'quantilesDoublesSketchToString',
            'name'  => $this->outputName,
            'field' => $this->dimension->toArray(),
        ];
    }
}