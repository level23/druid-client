<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class CdfPostAggregator implements PostAggregatorInterface
{
    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var PostAggregatorInterface
     */
    protected $dimension;

    /**
     * @var array
     */
    protected $splitPoints;

    /**
     * QuantilePostAggregator constructor.
     *
     * @param PostAggregatorInterface $dimension    Post aggregator that refers to a DoublesSketch (fieldAccess or
     *                                              another post aggregator)
     * @param string                  $outputName   The name as it will be used in our result.
     * @param array                   $splitPoints  Array of split points
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
     * @return array
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