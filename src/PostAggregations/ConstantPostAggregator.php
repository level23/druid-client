<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class ConstantPostAggregator implements PostAggregatorInterface
{
    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var int|float
     */
    protected $numericValue;

    /**
     * ConstantPostAggregator constructor.
     *
     * @param string    $outputName
     * @param int|float $numericValue
     */
    public function __construct(string $outputName, $numericValue)
    {
        $this->outputName   = $outputName;
        $this->numericValue = $numericValue;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
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