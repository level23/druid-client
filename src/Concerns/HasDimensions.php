<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use ArrayObject;
use InvalidArgumentException;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Dimensions\LookupDimension;
use Level23\Druid\Extractions\CascadeExtraction;
use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Extractions\TimeFormatExtraction;
use Level23\Druid\Types\DataType;

trait HasDimensions
{
    /**
     * @var array|DimensionInterface[]
     */
    protected $dimensions = [];

    /**
     * @return array|DimensionInterface[]
     */
    public function getDimensions(): array
    {
        return $this->dimensions;
    }

    /**
     * Select a dimension from our statistics. Possible use a lookup function to find the
     * real data which we want to use.
     *
     * @param array|\ArrayObject|string|DimensionInterface $dimension
     * @param string                                       $as         When dimensions is a string (the dimension), you
     *                                                                 can specify the alias output name here.
     * @param string|\Level23\Druid\Types\DataType         $outputType This can either be "long", "float" or "string"
     *
     * @return $this
     */
    public function select(
        $dimension,
        string $as = '',
        $outputType = 'string'
    ) {
        if (is_string($outputType) && !DataType::isValid($outputType)) {
            throw new InvalidArgumentException(
                'The given output type is invalid: ' . $outputType . '. ' .
                'Allowed are: ' . implode(',', DataType::values())
            );
        }

        if (is_string($dimension)) {
            $this->addDimension(new Dimension($dimension, ($as ?: $dimension), $outputType));
        } else {
            $this->addDimension($dimension);
        }

        return $this;
    }

    /**
     * Select a dimension while applying a time formatting over the dimension.
     *
     * For formatting, use the Joda DateTimeFormat documentation.
     *
     * @see http://www.joda.org/joda-time/apidocs/org/joda/time/format/DateTimeFormat.html
     *
     * @param string      $dimension   The dimension to select and apply the time format over.
     * @param string      $format      date time format for the resulting dimension value, in Joda Time DateTimeFormat,
     *                                 or null to use the default ISO8601 format.
     * @param string      $as          output name which is used in the result set.
     * @param string|null $granularity granularity to apply before formatting, or omit to not apply any granularity.
     * @param string|null $timeZone    time zone to use in IANA tz database format, e.g. Europe/Berlin (this can
     *                                 possibly be different than the aggregation time-zone)
     * @param string|null $locale      locale (language and country) to use, given as a IETF BCP 47 language tag, e.g.
     *                                 en-US, en-GB, fr-FR, fr-CA, etc
     * @param bool|null   $asMillis    boolean value, set to true to treat input strings as millis rather than ISO8601
     *                                 strings. Additionally, if format is null or not specified, output will be in
     *                                 millis rather than ISO8601.
     *
     * @return $this
     */
    public function extractTimeFormat(
        string $dimension,
        string $format = null,
        string $as = '',
        string $granularity = null,
        string $timeZone = null,
        string $locale = null,
        bool $asMillis = null
    ) {
        $this->dimensions[] = new Dimension(
            $dimension,
            ($as ?: $dimension),
            DataType::STRING(),
            new TimeFormatExtraction($format, $granularity, $locale, $timeZone, $asMillis)
        );

        return $this;
    }

    /**
     * Retrieve a dimension while extracting the "real" value from an registered lookup function.
     *
     * @param string    $dimension               The dimension to select
     * @param string    $lookupFunction          The name of the lookup function to use
     * @param string    $as                      The name as it will be used in the result set. When empty, we will use
     *                                           the original dimensions name.
     * @param bool      $retainMissingValue      Should we keep values which cannot be found in the lookup function?
     * @param string    $replaceMissingValueWith If missing items should be kept, they will be replaced with this value
     *                                           (or the original value if null).
     * @param string    $outputType
     * @param bool      $optimize
     * @param bool|null $injective
     *
     * @return $this
     */
    public function extractLookup(
        string $dimension,
        string $lookupFunction,
        string $as = '',
        bool $retainMissingValue = false,
        string $replaceMissingValueWith = '',
        $outputType = 'string',
        bool $optimize = true,
        bool $injective = null
    ) {
        $this->dimensions[] = new Dimension(
            $dimension,
            ($as ?: $dimension),
            $outputType,
            new LookupExtraction($lookupFunction, $retainMissingValue, $replaceMissingValueWith, $optimize, $injective)
        );

        return $this;
    }

    /**
     * Apply a set of extraction functions to the selected dimension.
     *
     * @param string          $dimension
     * @param string          $as
     * @param array           $extractions
     * @param string|DataType $outputType
     *
     * @return $this
     */
    public function extractCascade(string $dimension, string $as = '', array $extractions = [], $outputType = 'string')
    {
        $this->dimensions[] = new Dimension(
            $dimension,
            ($as ?: $dimension),
            $outputType,
            new CascadeExtraction($extractions)
        );

        return $this;
    }

    /**
     *
     * @param string $lookupFunction
     * @param string $dimension
     * @param string $as
     * @param bool   $replaceMissingValue
     *
     * @return $this
     */
    public function lookup(
        string $lookupFunction,
        string $dimension,
        string $as = '',
        $replaceMissingValue = false
    ) {
        $this->dimensions[] = new LookupDimension(
            $dimension,
            $lookupFunction,
            ($as ?: $dimension),
            $replaceMissingValue
        );

        return $this;
    }

    /**
     * Add a dimension or a set of dimensions to our dimension list.
     *
     * @param DimensionInterface|string|array|ArrayObject $dimension
     */
    protected function addDimension($dimension)
    {
        if ($dimension instanceof DimensionInterface) {
            $this->dimensions[] = $dimension;
        } elseif (is_string($dimension)) {
            $this->dimensions[] = new Dimension($dimension, $dimension);
        } elseif (is_iterable($dimension)) {
            foreach ($dimension as $key => $value) {
                if (is_string($key) && is_string($value)) {
                    $this->dimensions[] = new Dimension($key, $value);
                } else {
                    $this->addDimension($value);
                }
            }
        }
    }
}