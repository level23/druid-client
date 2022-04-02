<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class RegexExtraction implements ExtractionInterface
{
    protected string $regexp;

    protected int $groupToExtract;

    protected bool $replaceMissingValue;

    protected ?string $replaceMissingValueWith;

    /**
     * RegexExtraction constructor.
     *
     * @param string      $regexp
     * @param int         $groupToExtract      If "$groupToExtract" is set, it will control which group from the match
     *                                         to extract. Index zero extracts the string matching the entire pattern.
     * @param bool|string $keepMissingValue    When true, we will keep values which are not matched by the regexp. The
     *                                         value will be null. If false, the missing items will not be kept in the
     *                                         result set. If this is a string, we will keep the missing values and
     *                                         replace them with the string value.
     */
    public function __construct(
        string $regexp,
        int $groupToExtract = 1,
        $keepMissingValue = false
    ) {
        $this->regexp                  = $regexp;
        $this->groupToExtract          = $groupToExtract;
        $this->replaceMissingValue     = is_string($keepMissingValue) ? true : $keepMissingValue;
        $this->replaceMissingValueWith = is_string($keepMissingValue) ? $keepMissingValue : null;
    }

    /**
     * Return the Extraction Function, so it can be used in a druid query.
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