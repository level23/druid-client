<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

use Level23\Druid\Types\Granularity;

/**
 * Class TimeFormatExtraction
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
 * @package Level23\Druid\Extractions
 */
class TimeFormatExtraction implements ExtractionInterface
{
    protected ?string $format;

    protected ?Granularity $granularity;

    protected ?string $locale;

    protected ?string $timeZone;

    protected ?bool $asMillis;

    /**
     * TimeFormatExtraction constructor.
     *
     * @param string|null             $format         date time format for the resulting dimension value, in Joda Time
     *                                                DateTimeFormat, or null to use the default ISO8601 format.
     * @param string|Granularity|null $granularity    granularity to apply before formatting, or omit to not apply any
     *                                                granularity.
     * @param string|null             $locale         locale (language and country) to use, given as an IETF BCP 47
     *                                                language tag, e.g. en-US, en-GB, fr-FR, fr-CA, etc.
     * @param string|null             $timeZone       time zone to use in IANA tz database format, e.g. Europe/Berlin
     *                                                (this can possibly be different from the aggregation time-zone)
     * @param bool|null               $asMilliseconds boolean value, set to true to treat input strings as millis
     *                                                rather than ISO8601 strings. Additionally, if format is null or
     *                                                not specified, output will be in millis rather than ISO8601.
     */
    public function __construct(
        ?string $format = null,
        string|Granularity $granularity = null,
        ?string $locale = null,
        ?string $timeZone = null,
        ?bool $asMilliseconds = null
    ) {
        $this->format      = $format;
        $this->granularity = is_string($granularity) ? Granularity::from(strtolower($granularity)) : $granularity;
        $this->locale      = $locale;
        $this->timeZone    = $timeZone;
        $this->asMillis    = $asMilliseconds;
    }

    /**
     * Return the Extraction Function, so it can be used in a druid query.
     *
     * @return array<string,string|bool>
     */
    public function toArray(): array
    {
        $result = [
            'type' => 'timeFormat',
        ];

        $properties = ['format', 'granularity', 'locale', 'timeZone', 'asMillis'];

        foreach ($properties as $property) {
            if ($this->$property !== null) {
                if ($property == 'granularity') {
                    $result['granularity'] = $this->granularity?->value;
                } else {
                    $result[$property] = $this->$property;
                }
            }
        }

        return $result;
    }
}