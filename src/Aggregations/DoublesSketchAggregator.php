<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

class DoublesSketchAggregator implements AggregatorInterface
{
    /**
     * A String for the name of the input field (can contain sketches or raw numeric values).
     *
     * @var string
     */
    protected string $metricName;

    /**
     * A String for the output (result) name of the calculation.
     *
     * @var string
     */
    protected string $outputName;

    /**
     * Parameter that determines the accuracy and size of the sketch. Higher k means higher accuracy but more space to
     * store sketches. Must be a power of 2 from 2 to 32768. See accuracy information in the DataSketches documentation
     * for details.
     *
     * @var int|null
     */
    protected ?int $sizeAndAccuracy = null;

    /**
     * This parameter is a temporary solution to avoid a known issue. It may be removed in a future release after the
     * bug is fixed. This parameter defines the maximum number of items to store in each sketch. If a sketch reaches
     * the limit, the query can throw IllegalStateException. To workaround this issue, increase the maximum stream
     * length. See accuracy information in the DataSketches documentation for how many bytes are required per stream
     * length.
     *
     * @var int|null
     */
    protected ?int $maxStreamLength = null;

    /**
     * @param string   $metricName      A String for the name of the input field (can contain sketches or raw numeric
     *                                  values).
     * @param string   $outputName      A String for the output (result) name of the calculation.
     * @param int|null $sizeAndAccuracy Parameter that determines the accuracy and size of the sketch. Higher k means
     *                                  higher accuracy but more space to store sketches. Must be a power of 2 from 2
     *                                  to 32768. See accuracy information in the DataSketches documentation for
     *                                  details.
     * @param int|null $maxStreamLength This parameter is a temporary solution to avoid a known issue. It may be
     *                                  removed in a future release after the bug is fixed. This parameter defines the
     *                                  maximum number of items to store in each sketch. If a sketch reaches the limit,
     *                                  the query can throw IllegalStateException. To workaround this issue, increase
     *                                  the maximum stream length. See accuracy information in the DataSketches
     *                                  documentation for how many bytes are required per stream length.
     *
     * @see https://druid.apache.org/docs/latest/development/extensions-core/datasketches-quantiles.html
     */
    public function __construct(
        string $metricName,
        string $outputName = '',
        ?int $sizeAndAccuracy = null,
        ?int $maxStreamLength = null
    ) {
        $this->metricName      = $metricName;
        $this->outputName      = $outputName ?: $metricName;
        $this->sizeAndAccuracy = $sizeAndAccuracy;
        $this->maxStreamLength = $maxStreamLength;
    }

    public function toArray(): array
    {
        $result = [
            'type'      => 'quantilesDoublesSketch',
            'name'      => $this->outputName,
            'fieldName' => $this->metricName,
        ];

        if ($this->sizeAndAccuracy !== null) {
            $result['k'] = $this->sizeAndAccuracy;
        }

        if ($this->maxStreamLength !== null) {
            $result['maxStreamLength'] = $this->maxStreamLength;
        }

        return $result;
    }
}