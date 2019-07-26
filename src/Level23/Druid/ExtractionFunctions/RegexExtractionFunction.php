<?php

namespace Level23\Druid\ExtractionFunctions;

class RegexExtractionFunction implements ExtractionFunctionInterface
{
    /**
     * @var string
     */
    protected $regexp;

    /**
     * @var int
     */
    protected $groupToExtract;

    /**
     * @var bool
     */
    protected $replaceMissingValue;

    /**
     * @var string|null
     */
    protected $replaceMissingValueWith;

    /**
     * RegexExtractionFunction constructor.
     *
     * @param string      $regexp
     * @param int         $groupToExtract
     * @param bool        $replaceMissingValue
     * @param string|null $replaceMissingValueWith
     */
    public function __construct(
        string $regexp,
        $groupToExtract = 1,
        $replaceMissingValue = false,
        ?string $replaceMissingValueWith = null
    ) {
        $this->regexp                  = $regexp;
        $this->groupToExtract          = $groupToExtract;
        $this->replaceMissingValue     = $replaceMissingValue;
        $this->replaceMissingValueWith = $replaceMissingValueWith;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function getExtractionFunction(): array
    {
        return [
            'type'                    => 'regex',
            'expr'                    => $this->regexp,
            'index'                   => $this->groupToExtract,
            'replaceMissingValue'     => $this->replaceMissingValue,
            'replaceMissingValueWith' => $this->replaceMissingValueWith,
        ];
    }
}