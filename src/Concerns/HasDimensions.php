<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use ArrayObject;
use Level23\Druid\Types\DataType;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\LookupDimension;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Dimensions\ListFilteredDimension;
use Level23\Druid\Dimensions\RegexFilteredDimension;
use Level23\Druid\Dimensions\PrefixFilteredDimension;

trait HasDimensions
{
    /**
     * @var array|DimensionInterface[]
     */
    protected array $dimensions = [];

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
     * @param \ArrayObject<int|string,string>|string|DimensionInterface|array<int,string> $dimension
     * @param string                                                                      $as         When dimensions
     *                                                                                                is a string (the
     *                                                                                                dimension), you
     *                                                                                                can specify the
     *                                                                                                alias output name
     *                                                                                                here.
     * @param \Closure|null                                                               $extraction
     * @param string|DataType                                                             $outputType This can either
     *                                                                                                be
     *                                                                                                "long",
     *                                                                                                "float" or
     *                                                                                                "string"
     *
     * @return self
     */
    public function select(
        array|ArrayObject|string|DimensionInterface $dimension,
        string $as = '',
        Closure $extraction = null,
        string|DataType $outputType = DataType::STRING
    ): self {
        if (is_string($dimension)) {
            if (!empty($extraction)) {
                $builder = new ExtractionBuilder();
                call_user_func($extraction, $builder);

                $extraction = $builder->getExtraction();
            } else {
                $extraction = null;
            }

            $this->addDimension(new Dimension(
                $dimension,
                ($as ?: $dimension),
                is_string($outputType) ? DataType::from(strtolower($outputType)) : $outputType,
                $extraction
            ));
        } else {
            $this->addDimension($dimension);
        }

        return $this;
    }

    /**
     * Select a dimension and transform it using a lookup function.
     *
     * @param string      $lookupFunction      The name of the registered lookup function which you want to use
     * @param string      $dimension           The dimension which you want to transform using the lookup function.
     * @param string      $as                  The name as it will be used in the result set. If left empty, we will
     *                                         use the same name as the dimension.
     * @param bool|string $keepMissingValue    When true, we will keep values which are not known in the lookup
     *                                         function. The original value will be kept. If false, the missing items
     *                                         will not be kept in the result set. If this is a string, we will keep
     *                                         the missing values and replace them with the string value.
     *
     * @return self
     */
    public function lookup(
        string $lookupFunction,
        string $dimension,
        string $as = '',
        bool|string $keepMissingValue = false
    ): self {
        $this->addDimension(new LookupDimension(
            $dimension,
            $lookupFunction,
            ($as ?: $dimension),
            $keepMissingValue
        ));

        return $this;
    }

    /**
     * Select a dimension and transform it using the given lookup map.
     *
     * @param array<string,string> $map              A list with key = value items, where the dimension value will be
     *                                               looked up in the map, and be replaced by the value in the map.
     * @param string               $dimension        The dimension which you want to transform using the lookup
     *                                               function.
     * @param string               $as               The name as it will be used in the result set. If left empty, we
     *                                               will use the same name as the dimension.
     * @param bool|string          $keepMissingValue When true, we will keep values which are not known in the lookup
     *                                               function. The original value will be kept. If false, the missing
     *                                               items will not be kept in the result set. If this is a string, we
     *                                               will keep the missing values and replace them with the string
     *                                               value.
     * @param bool                 $isOneToOne       Set to true if the key/value items are unique in the given map.
     *
     * @return self
     */
    public function inlineLookup(
        array $map,
        string $dimension,
        string $as = '',
        bool|string $keepMissingValue = false,
        bool $isOneToOne = false
    ): self {
        $this->addDimension(new LookupDimension(
            $dimension,
            $map,
            ($as ?: $dimension),
            $keepMissingValue,
            $isOneToOne
        ));

        return $this;
    }

    /**
     * For a multi value field, you can filter which values should be returned based in the given list.
     *
     * @see: https://druid.apache.org/docs/latest/querying/multi-value-dimensions.html
     *
     * @param string   $dimension   The name of the multi-value dimension where you want to select data from
     * @param string[] $values      A list of items which you want to select (whitelist) or not select (blacklist)
     * @param string   $as          The name as it will be used in the result set. If left empty, we will use the same
     *                              name as the dimension.
     * @param string|DataType   $outputType  This can either be "long", "float" or "string"
     * @param bool     $isWhitelist Whether the list is a whitelist (true) or a blacklist (false)
     *
     * @return self
     */
    public function multiValueListSelect(
        string $dimension,
        array $values,
        string $as = '',
        string|DataType $outputType = DataType::STRING,
        bool $isWhitelist = true
    ): self {
        $this->addDimension(new ListFilteredDimension(
            new Dimension(
                $dimension,
                $as ?: $dimension,
                $outputType
            ),
            $values,
            $isWhitelist
        ));

        return $this;
    }

    /**
     * For a multi value field, you can filter which values should be returned based on the given java regular
     * expression.
     *
     * @see: https://druid.apache.org/docs/latest/querying/multi-value-dimensions.html
     *
     * @param string $dimension   The name of the multi-value dimension where you want to select data from
     * @param string $regex       Only return the items in this dimension which match with the given java regex.
     * @param string $as          The name as it will be used in the result set. If left empty, we will use the same
     *                            name as the dimension.
     * @param string|DataType $outputType  This can either be "long", "float" or "string"
     *
     * @return self
     */
    public function multiValueRegexSelect(
        string $dimension,
        string $regex,
        string $as = '',
        string|DataType $outputType = DataType::STRING
    ): self {
        $this->addDimension(new RegexFilteredDimension(
            new Dimension(
                $dimension,
                $as ?: $dimension,
                $outputType
            ),
            $regex
        ));

        return $this;
    }

    /**
     * For a multi value field, you can filter which values should be returned based on the given prefix.
     *
     * @see: https://druid.apache.org/docs/latest/querying/multi-value-dimensions.html
     *
     * @param string $dimension   The name of the multi-value dimension where you want to select data from
     * @param string $prefix      Only return the values which match with the given prefix.
     * @param string $as          The name as it will be used in the result set. If left empty, we will use the same
     *                            name as the dimension.
     * @param string|DataType $outputType  This can either be "long", "float" or "string"
     *
     * @return self
     */
    public function multiValuePrefixSelect(
        string $dimension,
        string $prefix,
        string $as = '',
        string|DataType $outputType = DataType::STRING
    ): self {
        $this->addDimension(new PrefixFilteredDimension(
            new Dimension(
                $dimension,
                $as ?: $dimension,
                $outputType
            ),
            $prefix
        ));

        return $this;
    }

    /**
     * Add a dimension or a set of dimensions to our dimension list.
     *
     * @param ArrayObject<int|string,string>|string|DimensionInterface|array<int,string> $dimension
     */
    protected function addDimension(array|ArrayObject|string|DimensionInterface $dimension): void
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