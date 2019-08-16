<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

class DistinctCountAggregator implements AggregatorInterface
{
    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var string
     */
    protected $dimension;

    /**
     * Must be a power of 2. Internally, size refers to the maximum number of entries sketch object will retain.
     * Higher size means higher accuracy but more space to store sketches.
     * Note that after you index with a particular size, druid will persist sketch in segments and you will
     * use size greater or equal to that at query time. See the DataSketches site for details.
     * In general, We recommend just sticking to default size.
     *
     * @var int
     */
    protected $size = 16384;

    /**
     * CountAggregator constructor.
     *
     * @param string $dimension
     * @param string $outputName
     * @param int    $size
     */
    public function __construct(string $dimension, string $outputName, int $size = 16384)
    {
        $this->outputName = $outputName;
        $this->dimension  = $dimension;
        $this->size       = $size;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'               => 'thetaSketch',
            'name'               => $this->outputName,
            'fieldName'          => $this->dimension,
            'isInputThetaSketch' => false,
            'size'               => $this->size,
        ];
    }
}