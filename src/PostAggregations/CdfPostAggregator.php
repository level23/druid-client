<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class CdfPostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected PostAggregatorInterface $dimension;

    /**
     * @var float[]
     */
    protected array $splitPoints;

    /**
     * QuantilePostAggregator constructor.
     *
     * @param PostAggregatorInterface $dimension    Post aggregator that refers to a DoublesSketch (fieldAccess or
     *                                              another post aggregator)
     * @param string                  $outputName   The name as it will be used in our result.
     * @param float[]                 $splitPoints  Array of split points
     */
    public function __construct(PostAggregatorInterface $dimension, string $outputName, array $splitPoints)
    {
        $this->outputName  = $outputName;
        $this->dimension   = $dimension;
        $this->splitPoints = $splitPoints;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array<string,string|float[]|array<string,string|array<mixed>>>
     */
    public function toArray(): array
    {
        return [
            'type'        => 'quantilesDoublesSketchToCDF',
            'name'        => $this->outputName,
            'field'       => $this->dimension->toArray(),
            'splitPoints' => $this->splitPoints,
        ];
    }
}