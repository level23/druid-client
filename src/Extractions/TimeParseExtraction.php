<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

/**
 * Class TimeParseExtraction
 *
 * Parses dimension values as timestamps using the given input format, and returns them formatted using the given
 * output format.
 *
 * Note, if you are working with the __time dimension, you should consider using the timeFormat extraction function
 * instead, which works on time value directly as opposed to string values.
 *
 * @package Level23\Druid\Extractions
 */
class TimeParseExtraction implements ExtractionInterface
{
    /**
     * @var string
     */
    protected $inputFormat;

    /**
     * @var string
     */
    protected $outputFormat;

    /**
     * @var bool
     */
    protected $jodaFormat;

    /**
     * TimeParseExtraction constructor.
     *
     * If "joda" is true, time formats are described in the Joda DateTimeFormat documentation. If "joda" is false (or
     * unspecified) then formats are described in the SimpleDateFormat documentation. In general, we recommend setting
     * "joda" to true since Joda format strings are more common in Druid APIs and since Joda handles certain edge cases
     * (like weeks and week-years near the start and end of calendar years) in a more ISO8601 compliant way.
     *
     * If a value cannot be parsed using the provided timeFormat, it will be returned as-is.
     *
     * @param string $inputFormat  Parse the string value using the given format, then format it as the $outputFormat.
     * @param string $outputFormat The format as you which to receive the result in.
     * @param bool   $jodaFormat
     *
     * @see http://www.joda.org/joda-time/apidocs/org/joda/time/format/DateTimeFormat.html
     * @see http://icu-project.org/apiref/icu4j/com/ibm/icu/text/SimpleDateFormat.html
     */
    public function __construct(string $inputFormat, string $outputFormat, bool $jodaFormat = true)
    {
        $this->inputFormat  = $inputFormat;
        $this->outputFormat = $outputFormat;
        $this->jodaFormat   = $jodaFormat;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'         => 'time',
            'timeFormat'   => $this->inputFormat,
            'resultFormat' => $this->outputFormat,
            'joda'         => $this->jodaFormat,
        ];
    }
}