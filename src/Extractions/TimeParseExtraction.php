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
     * TimeParseExtraction constructor.
     *
     * @param string $inputFormat  Parse the string value using the given format, then format it as the $outputFormat.
     * @param string $outputFormat The format as you which to receive the result in.
     *
     * @see http://icu-project.org/apiref/icu4j/com/ibm/icu/text/SimpleDateFormat.html
     */
    public function __construct(string $inputFormat, string $outputFormat)
    {
        $this->inputFormat  = $inputFormat;
        $this->outputFormat = $outputFormat;
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
        ];
    }
}