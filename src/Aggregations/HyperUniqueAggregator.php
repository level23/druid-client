<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

/**
 * Class HyperUniqueAggregator
 *
 * @package Level23\Druid\Aggregations
 * @see     https://druid.apache.org/docs/latest/querying/hll-old.html
 */
class HyperUniqueAggregator implements AggregatorInterface
{
    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var string
     */
    protected $metricName;

    /**
     * @var bool
     */
    protected $isInputHyperUnique;

    /**
     * @var bool
     */
    protected $round;

    /**
     * HyperUniqueAggregator constructor.
     *
     * @param string $outputName
     * @param string $metricName
     * @param bool   $isInputHyperUnique
     * @param bool   $round
     */
    public function __construct(
        string $outputName,
        string $metricName,
        bool $isInputHyperUnique = false,
        bool $round = false
    ) {
        $this->outputName         = $outputName;
        $this->metricName         = $metricName;
        $this->isInputHyperUnique = $isInputHyperUnique;
        $this->round              = $round;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'               => 'hyperUnique',
            'name'               => $this->outputName,
            'fieldName'          => $this->metricName,
            'isInputHyperUnique' => $this->isInputHyperUnique,
            'round'              => $this->round,
        ];
    }
}