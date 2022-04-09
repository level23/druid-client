<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class HistogramPostAggregator implements PostAggregatorInterface
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
     * @var array|null
     */
    protected $splitPoints;

    /**
     * @var int|null
     */
    protected $numBins;

    /**
     * HistogramPostAggregator constructor.
     *
     * @param PostAggregatorInterface $dimension     Post aggregator that refers to a DoublesSketch (fieldAccess or
     *                                               another post aggregator)
     * @param string                  $outputName
     * @param array|null              $splitPoints   array of split points (optional)
     * @param int|null                $numBins       number of bins (optional, defaults to 10)
     */
    public function __construct(
        PostAggregatorInterface $dimension,
        string $outputName,
        array $splitPoints = null,
        ?int $numBins = null
    ) {
        $this->outputName  = $outputName;
        $this->dimension   = $dimension;
        $this->splitPoints = $splitPoints;
        $this->numBins     = $numBins;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type'  => 'quantilesDoublesSketchToHistogram',
            'name'  => $this->outputName,
            'field' => $this->dimension->toArray(),
        ];

        if ($this->splitPoints) {
            $result['splitPoints'] = $this->splitPoints;
        }

        if ($this->numBins) {
            $result['numBins'] = $this->numBins;
        }

        return $result;
    }
}