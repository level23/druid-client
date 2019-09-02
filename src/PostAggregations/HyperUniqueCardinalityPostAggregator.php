<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class HyperUniqueCardinalityPostAggregator implements PostAggregatorInterface
{
    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * HyperUniqueCardinalityPostAggregator constructor.
     *
     * @param string $outputName The output name
     * @param string $fieldName  The name field value of the hyperUnique aggregator
     */
    public function __construct(string $outputName, string $fieldName)
    {
        $this->outputName = $outputName;
        $this->fieldName  = $fieldName;
    }

    /**
     * Return the post aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'      => 'hyperUniqueCardinality',
            'name'      => $this->outputName,
            'fieldName' => $this->fieldName,
        ];
    }
}