<?php

namespace Level23\Druid\ExtractionFunctions;

/**
 * Class TimeFormatExtractionFunction
 *
 * Time Format Extraction Function
 *
 * Returns the dimension value formatted according to the
 * given format string, time zone, and locale.
 *
 * For __time dimension values, this formats the time value
 * bucketed by the aggregation granularity
 *
 * For a regular dimension, it assumes the string is formatted in ISO-8601 date and time format.
 *
 * @package Level23\Druid\ExtractionFunctions
 */
class TimeFormatExtractionFunction implements ExtractionFunctionInterface
{
    /**
     * @var string|null
     */
    protected $format;

    /**
     * @var string|null
     */
    protected $granularity;

    /**
     * @var string|null
     */
    protected $locale;

    /**
     * @var string|null
     */
    protected $timeZone;

    /**
     * @var bool|null
     */
    protected $asMillis;

    /**
     * TimeFormatExtractionFunction constructor.
     *
     * @param string|null $format         date time format for the resulting dimension value, in Joda Time
     *                                    DateTimeFormat, or null to use the default ISO8601 format.
     * @param string|null $granularity    granularity to apply before formatting, or omit to not apply any granularity.
     * @param string|null $locale         locale (language and country) to use, given as a IETF BCP 47 language tag,
     *                                    e.g. en-US, en-GB, fr-FR, fr-CA, etc.
     * @param string|null $timeZone       time zone to use in IANA tz database format, e.g. Europe/Berlin (this can
     *                                    possibly be different than the aggregation time-zone)
     * @param bool|null   $asMilliseconds boolean value, set to true to treat input strings as millis rather than
     *                                    ISO8601 strings. Additionally, if format is null or not specified, output
     *                                    will be in millis rather than ISO8601.
     */
    public function __construct(
        ?string $format = null,
        ?string $granularity = null,
        ?string $locale = null,
        ?string $timeZone = null,
        ?bool $asMilliseconds = null
    ) {
        $this->format      = $format;
        $this->granularity = $granularity;
        $this->locale      = $locale;
        $this->timeZone    = $timeZone;
        $this->asMillis    = $asMilliseconds;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function getExtractionFunction(): array
    {
        $result = [
            'type' => 'timeFormat',
        ];

        $properties = ['format', 'granularity', 'locale', 'timeZone', 'asMillis'];

        foreach ($properties as $property) {
            if ($this->$property !== null) {
                $result[$property] = $this->$property;
            }
        }

        return $result;
    }
}