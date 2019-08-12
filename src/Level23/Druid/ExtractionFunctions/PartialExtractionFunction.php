<?php
declare(strict_types=1);

namespace Level23\Druid\ExtractionFunctions;

/**
 * Class PartialExtractionFunction
 *
 * Returns the dimension value unchanged if the regular expression matches, otherwise returns null.
 *
 * @package Level23\Druid\ExtractionFunctions
 */
class PartialExtractionFunction implements ExtractionFunctionInterface
{
    /**
     * @var string
     */
    protected $regularExpression;

    /**
     * PartialExtractionFunction constructor.
     *
     * @param string $regularExpression A Java regex pattern
     *
     * @see http://docs.oracle.com/javase/6/docs/api/java/util/regex/Pattern.html
     */
    public function __construct(string $regularExpression)
    {
        $this->regularExpression = $regularExpression;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function getExtractionFunction(): array
    {
        $response = [
            'type' => 'partial',
            'expr' => $this->regularExpression,
        ];

        return $response;
    }
}