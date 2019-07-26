<?php

namespace Level23\Druid\Dimensions;

use Level23\Druid\ExtractionFunctions\ExtractionFunctionInterface;

class ExtractionDimension implements DimensionInterface
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
     * @var \Level23\Druid\ExtractionFunctions\ExtractionFunctionInterface
     */
    protected $extractionFunction;

    /**
     * DefaultDimension constructor.
     *
     * @param string                                                         $dimension
     * @param \Level23\Druid\ExtractionFunctions\ExtractionFunctionInterface $extractionFunction
     * @param string                                                         $outputName
     * @param string|null                                                    $outputType
     */
    public function __construct(
        string $dimension,
        ExtractionFunctionInterface $extractionFunction,
        string $outputName = '',
        ?string $outputType = null
    ) {
        $this->dimension          = $dimension;
        $this->outputName         = $outputName ?: $dimension;
        $this->outputType         = $outputType;
        $this->extractionFunction = $extractionFunction;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array
     */
    public function getDimension(): array
    {
        $result = [
            'type'         => 'extraction',
            'dimension'    => $this->dimension,
            'outputName'   => $this->outputName,
            'extractionFn' => $this->extractionFunction->getExtractionFunction(),
        ];

        if ($this->outputType) {
            $result['outputType'] = $this->outputType;
        }

        return $result;
    }
}