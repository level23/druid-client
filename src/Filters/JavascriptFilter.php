<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Extractions\ExtractionInterface;

/**
 * Class JavascriptFilter
 *
 * The JavaScript filter matches a dimension against the specified JavaScript function predicate.
 * The filter matches values for which the function returns true.
 *
 * The function takes a single argument, the dimension value, and returns either true or false.
 *
 * @package Level23\Druid\Filters
 */
class JavascriptFilter implements FilterInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var string
     */
    protected $javascriptFunction;

    /**
     * @var \Level23\Druid\Extractions\ExtractionInterface|null
     */
    protected $extractionFunction;

    /**
     * JavascriptFilter constructor.
     *
     * @param string                   $dimension
     * @param string                   $javascriptFunction
     * @param ExtractionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        string $javascriptFunction,
        ExtractionInterface $extractionFunction = null
    ) {
        $this->dimension          = $dimension;
        $this->javascriptFunction = $javascriptFunction;
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
            'type'      => 'javascript',
            'dimension' => $this->dimension,
            'function'  => $this->javascriptFunction,
        ];

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->getExtractionFunction();
        }

        return $result;
    }
}