<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class RegexExtraction implements ExtractionInterface
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
     * RegexExtraction constructor.
     *
     * @param string      $regexp
     * @param int         $groupToExtract
     * @param bool|string $replaceMissingValue When true, we will keep values which are not matched by the regexp. The
     *                                         value will be null. If false, the missing items will not be kept in the
     *                                         result set. If this is a string, we will keep the missing values and
     *                                         replace them with the string value.
     */
    public function __construct(
        string $regexp,
        $groupToExtract = 1,
        $replaceMissingValue = false
    ) {
        $this->regexp                  = $regexp;
        $this->groupToExtract          = $groupToExtract;
        $this->replaceMissingValue     = is_string($replaceMissingValue) ? true : $replaceMissingValue;
        $this->replaceMissingValueWith = is_string($replaceMissingValue) ? $replaceMissingValue : null;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
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