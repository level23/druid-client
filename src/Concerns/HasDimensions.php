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
     * @param string[]|\ArrayObject<int|string, string>|string|DimensionInterface $dimension
     * @param string                                                              $as         When dimensions is a
     *                                                                                        string (the dimension),
     *                                                                                        you can specify the alias
     *                                                                                        output name here.
     * @param \Closure|null                                                       $extraction
     * @param string                                                              $outputType This can either be
     *                                                                                        "long",
     *                                                                                        "float" or "string"
     *
     * @return $this
     */
    public function select(
        $dimension,
        string $as = '',
        Closure $extraction = null,
        string $outputType = DataType::STRING
    ): self {
        if (is_string($dimension)) {
            if (!empty($extraction)) {
                $builder = new ExtractionBuilder();
                call_user_func($extraction, $builder);

                $extraction = $builder->getExtraction();
            } else {
                $extraction = null;
            }

            $this->addDimension(
                new Dimension($dimension, ($as ?: $dimension), DataType::validate($outputType), $extraction)
            );
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
     * @return $this
     */
    public function lookup(
        string $lookupFunction,
        string $dimension,
        string $as = '',
        $keepMissingValue = false
    ): self {
        $this->dimensions[] = new LookupDimension(
            $dimension,
            $lookupFunction,
            ($as ?: $dimension),
            $keepMissingValue
        );

        return $this;
    }

    /**
     * Add a dimension or a set of dimensions to our dimension list.
     *
     * @param DimensionInterface|string|string[]|ArrayObject<int|string,string> $dimension
     */
    protected function addDimension($dimension): void
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