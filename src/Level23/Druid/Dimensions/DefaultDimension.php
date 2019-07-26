<?php

namespace Level23\Druid\Dimensions;

class DefaultDimension implements DimensionInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var string|null
     */
    protected $outputType;

    /**
     * DefaultDimension constructor.
     *
     * @param string      $dimension
     * @param string      $outputName
     * @param string|null $outputType
     */
    public function __construct(string $dimension, string $outputName = '', ?string $outputType = null)
    {
        $this->dimension  = $dimension;
        $this->outputName = $outputName ?: $dimension;
        $this->outputType = $outputType;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array
     */
    public function getDimension(): array
    {
        $result = [
            'type'       => 'default',
            'dimension'  => $this->dimension,
            'outputName' => $this->outputName,
        ];

        if ($this->outputType) {
            $result['outputType'] = $this->outputType;
        }

        return $result;
    }
}