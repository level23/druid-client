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
     * @param string $fieldName  The name field value of the hyperUnique aggregator
     * @param string $outputName The output name
     */
    public function __construct(string $fieldName, string $outputName = null)
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
        $result = [
            'type'      => 'hyperUniqueCardinality',
            'fieldName' => $this->fieldName,
        ];

        if ($this->outputName) {
            $result['name'] = $this->outputName;
        }

        return $result;
    }
}