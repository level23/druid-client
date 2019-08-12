<?php

namespace Level23\Druid\Filters;

use Level23\Druid\ExtractionFunctions\ExtractionFunctionInterface;

class SelectorFilter implements FilterInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var \Level23\Druid\ExtractionFunctions\ExtractionFunctionInterface|null
     */
    protected $extractionFunction;

    /**
     * InFilter constructor.
     *
     * @param string                           $dimension
     * @param string                           $value
     * @param ExtractionFunctionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        string $value,
        ExtractionFunctionInterface $extractionFunction = null
    ) {
        $this->value              = $value;
        $this->dimension          = $dimension;
        $this->extractionFunction = $extractionFunction;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function getFilter(): array
    {
        $result = [
            'type'      => 'selector',
            'dimension' => $this->dimension,
            'value'     => $this->value,
        ];

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->getExtractionFunction();
        }

        return $result;
    }
}