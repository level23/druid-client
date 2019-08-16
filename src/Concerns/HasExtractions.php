<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Extractions\CascadeExtraction;
use Level23\Druid\Extractions\ExtractionInterface;
use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Extractions\PartialExtraction;
use Level23\Druid\Extractions\RegexExtraction;
use Level23\Druid\Extractions\SearchQueryExtraction;
use Level23\Druid\Extractions\SubstringExtraction;
use Level23\Druid\Extractions\TimeFormatExtraction;

trait HasExtractions
{
    /**
     * @var ExtractionInterface|null
     */
    protected $extraction;

    /**
     * @param string      $lookupName
     * @param bool|string $replaceMissingValue When true, we will keep values which are not known in the lookup
     *                                         function. The original value will be kept. If false, the missing items
     *                                         will not be kept in the result set. If this is a string, we will keep
     *                                         the missing values and replace them with the string value.
     * @param bool        $optimize
     * @param bool|null   $injective           A property of injective can override the lookup's own sense of whether
     *                                         or not it is injective. If left unspecified, Druid will use the
     *                                         registered cluster-wide lookup configuration.
     *
     * @return $this
     */
    public function lookup(
        string $lookupName,
        $replaceMissingValue = true,
        bool $optimize = true,
        ?bool $injective = null
    ) {
        $this->addExtraction(new LookupExtraction($lookupName, $replaceMissingValue, $optimize, $injective));

        return $this;
    }

    /**
     * @param string $regularExpression A Java regex pattern
     *
     * @return $this
     * @see http://docs.oracle.com/javase/6/docs/api/java/util/regex/Pattern.html
     */
    public function partial(string $regularExpression)
    {
        $this->addExtraction(new PartialExtraction($regularExpression));

        return $this;
    }

    /**
     * RegexExtraction constructor.
     *
     * @param string      $regexp
     * @param int         $groupToExtract
     * @param bool|string $replaceMissingValue When true, we will keep values which are not matched by the regexp. The
     *                                 value will be null. If false, the missing items will not be kept in the
     *                                 result set. If this is a string, we will keep the missing values and replace them
     *                                 with the string value.
     *
     * @return $this
     */
    public function regex(string $regexp, $groupToExtract = 1, $replaceMissingValue = true)
    {
        $this->addExtraction(new RegexExtraction($regexp, $groupToExtract, $replaceMissingValue));

        return $this;
    }

    /**
     * SearchQueryExtraction
     *
     * @param string|array $valueOrValues
     * @param bool         $caseSensitive
     *
     * @return $this
     */
    public function searchQuery($valueOrValues, bool $caseSensitive = false)
    {
        $this->addExtraction(new SearchQueryExtraction($valueOrValues, $caseSensitive));

        return $this;
    }

    /**
     * @param int      $index
     * @param int|null $length
     *
     * @return $this
     */
    public function substring(int $index, ?int $length = null)
    {
        $this->addExtraction(new SubstringExtraction($index, $length));

        return $this;
    }

    /**
     * TimeFormat extraction
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
     *
     * @return $this;
     */
    public function timeFormat(
        ?string $format = null,
        ?string $granularity = null,
        ?string $locale = null,
        ?string $timeZone = null,
        ?bool $asMilliseconds = null
    ) {
        $this->addExtraction(new TimeFormatExtraction($format, $granularity, $locale, $timeZone, $asMilliseconds));

        return $this;
    }

    /**
     * Add an extraction
     *
     * @param \Level23\Druid\Extractions\ExtractionInterface $extraction
     */
    protected function addExtraction($extraction)
    {
        if ($this->extraction === null) {
            $this->extraction = $extraction;

            return;
        }

        if ($this->extraction instanceof CascadeExtraction) {
            $this->extraction->addExtraction($extraction);
        } else {
            $extractions = [$this->extraction, $extraction];

            $this->extraction = new CascadeExtraction(...$extractions);
        }
    }

    /**
     * @return \Level23\Druid\Extractions\ExtractionInterface|null
     */
    public function getExtraction(): ?ExtractionInterface
    {
        return $this->extraction;
    }
}