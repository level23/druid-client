<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Types\NullHandling;
use Level23\Druid\Extractions\RegexExtraction;
use Level23\Druid\Extractions\UpperExtraction;
use Level23\Druid\Extractions\LowerExtraction;
use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Extractions\BucketExtraction;
use Level23\Druid\Extractions\PartialExtraction;
use Level23\Druid\Extractions\CascadeExtraction;
use Level23\Druid\Extractions\ExtractionInterface;
use Level23\Druid\Extractions\SubstringExtraction;
use Level23\Druid\Extractions\TimeParseExtraction;
use Level23\Druid\Extractions\TimeFormatExtraction;
use Level23\Druid\Extractions\JavascriptExtraction;
use Level23\Druid\Extractions\SearchQueryExtraction;
use Level23\Druid\Extractions\InlineLookupExtraction;
use Level23\Druid\Extractions\StringFormatExtraction;

trait HasExtractions
{
    /**
     * @var ExtractionInterface|null
     */
    protected $extraction;

    /**
     * @param string      $lookupName
     * @param bool|string $keepMissingValue    When true, we will keep values which are not known in the lookup
     *                                         function. The original value will be kept. If false, the missing items
     *                                         will not be kept in the result set. If this is a string, we will keep
     *                                         the missing values and replace them with the string value.
     * @param bool        $optimize            When set to true, we allow the optimization layer (which will run on the
     *                                         broker) to rewrite the extraction filter if needed.
     * @param bool|null   $injective           This can override the lookup's own sense of whether
     *                                         or not it is injective. If left unspecified, Druid will use the
     *                                         registered cluster-wide lookup configuration.
     *
     * @return $this
     */
    public function lookup(
        string $lookupName,
        $keepMissingValue = false,
        bool $optimize = true,
        bool $injective = null
    ) {
        $this->addExtraction(new LookupExtraction($lookupName, $keepMissingValue, $optimize, $injective));

        return $this;
    }

    /**
     * @param array       $map                 A map with items. The key is the value of the given dimension. It will
     *                                         be replaced by the value.
     * @param bool|string $keepMissingValue    When true, we will keep values which are not known in the lookup
     *                                         function. The original value will be kept. If false, the missing items
     *                                         will not be kept in the result set. If this is a string, we will keep
     *                                         the missing values and replace them with the string value.
     * @param bool        $optimize            When set to true, we allow the optimization layer (which will run on the
     *                                         broker) to rewrite the extraction filter if needed.
     * @param bool|null   $injective           Whether or not this list is injective. Injective lookups should include
     *                                         all possible keys that may show up in your dataset, and should also map
     *                                         all keys to unique values. This matters because non-injective lookups
     *                                         may map different keys to the same value, which must be accounted for
     *                                         during aggregation, lest query results contain two result values that
     *                                         should have been aggregated into one.
     *
     * @return $this
     */
    public function inlineLookup(
        array $map,
        $keepMissingValue = false,
        bool $optimize = true,
        bool $injective = null
    ) {
        $this->addExtraction(new InlineLookupExtraction($map, $keepMissingValue, $optimize, $injective));

        return $this;
    }

    /**
     * Returns the dimension value formatted according to the given format string.
     *
     * For example if you want to concat "[" and "]" before and after the actual dimension value, you need to specify
     * "[%s]" as format string.
     *
     * @param string $sprintfExpression
     * @param string $nullHandling Can be one of nullString, emptyString or returnNull. With "[%s]" format, each
     *                             configuration will result [null], [], null. Default is nullString.
     *
     * @return $this
     */
    public function format(string $sprintfExpression, string $nullHandling = NullHandling::NULL_STRING)
    {
        $this->addExtraction(new StringFormatExtraction($sprintfExpression, $nullHandling));

        return $this;
    }

    /**
     * Returns the dimension values as all upper case. Optionally user can specify the language to use in
     * order to perform upper transformation
     *
     * @param string|null $locale
     *
     * @return $this
     */
    public function upper(string $locale = null)
    {
        $this->addExtraction(new UpperExtraction($locale));

        return $this;
    }

    /**
     * Returns the dimension values as all lower case. Optionally user can specify the language to use in
     * order to perform lower transformation
     *
     * @param string|null $locale
     *
     * @return $this
     */
    public function lower(string $locale = null)
    {
        $this->addExtraction(new LowerExtraction($locale));

        return $this;
    }

    /**
     * Parses dimension values as timestamps using the given input format, and returns them formatted using the given
     * output format.
     *
     * Note, if you are working with the __time dimension, you should consider using the timeFormat extraction function
     * instead, which works on time value directly as opposed to string values.
     *
     * If "$jodaFormat" is true, time formats are described in the Joda DateTimeFormat documentation. If "joda" is
     * false (or unspecified) then formats are described in the SimpleDateFormat documentation. In general, we
     * recommend setting
     * "joda" to true since Joda format strings are more common in Druid APIs and since Joda handles certain edge cases
     * (like weeks and week-years near the start and end of calendar years) in a more ISO8601 compliant way.
     *
     * If a value cannot be parsed using the provided timeFormat, it will be returned as-is.
     *
     * @param string $inputFormat
     * @param string $outputFormat
     * @param bool   $jodaFormat  If true, we assume that the given formats are Joda DateTime Format. If false, we
     *                            assume that are SimpleDateFormat's.
     *
     * @return $this
     */
    public function timeParse(string $inputFormat, string $outputFormat, bool $jodaFormat = true)
    {
        $this->addExtraction(new TimeParseExtraction($inputFormat, $outputFormat, $jodaFormat));

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
     * @param int         $groupToExtract      If "$groupToExtract" is set, it will control which group from the match
     *                                         to extract. Index zero extracts the string matching the entire pattern.
     * @param bool|string $keepMissingValue    When true, we will keep values which are not matched by the regexp. The
     *                                         value will be null. If false, the missing items will not be kept in the
     *                                         result set. If this is a string, we will keep the missing values and
     *                                         replace them with the string value.
     *
     * @return $this
     */
    public function regex(string $regexp, int $groupToExtract = 1, $keepMissingValue = true)
    {
        $this->addExtraction(new RegexExtraction($regexp, $groupToExtract, $keepMissingValue));

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
        string $format = null,
        string $granularity = null,
        string $locale = null,
        string $timeZone = null,
        bool $asMilliseconds = null
    ) {
        $this->addExtraction(new TimeFormatExtraction($format, $granularity, $locale, $timeZone, $asMilliseconds));

        return $this;
    }

    /**
     * Add a javascript extraction
     *
     * @param string $javascript A javascript function which will receive the dimension/value. The function can then
     *                           extract the needed value from it and should return it.
     * @param bool   $injective  A property of injective specifies if the javascript function preserves uniqueness. The
     *                           default value is false meaning uniqueness is not preserved
     *
     * @return $this
     */
    public function javascript(string $javascript, bool $injective = false)
    {
        $this->addExtraction(new JavascriptExtraction($javascript, $injective));

        return $this;
    }

    /**
     * Bucket extraction function is used to bucket numerical values in each range of the given size by converting them
     * to the same base value. Non numeric values are converted to null.
     *
     * The following extraction function creates buckets of 5 starting from 2. In this case, values in the range of [2,
     * 7) will be converted to 2, values in [7, 12) will be converted to 7, etc.
     *
     * ```
     * bucket(5, 2);
     * ```
     *
     * @param int $size   the size of the buckets (optional, default 1)
     * @param int $offset the offset for the buckets (optional, default 0)
     *
     * @return $this
     */
    public function bucket(int $size = 1, int $offset = 0)
    {
        $this->addExtraction(new BucketExtraction($size, $offset));

        return $this;
    }

    /**
     * Add an extraction
     *
     * @param \Level23\Druid\Extractions\ExtractionInterface $extraction
     */
    protected function addExtraction(ExtractionInterface $extraction)
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