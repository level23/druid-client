<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use ArrayObject;
use Closure;
use InvalidArgumentException;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Dimensions\LookupDimension;
use Level23\Druid\ExtractionBuilder;
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
     * @param \Closure|null                                $extraction
     * @param string|\Level23\Druid\Types\DataType         $outputType This can either be "long", "float" or "string"
     *
     * @return $this
     */
    public function select(
        $dimension,
        string $as = '',
        Closure $extraction = null,
        $outputType = 'string'
    ) {
        if (is_string($outputType) && !DataType::isValid($outputType = strtolower($outputType))) {
            throw new InvalidArgumentException(
                'The given output type is invalid: ' . $outputType . '. ' .
                'Allowed are: ' . implode(',', DataType::values())
            );
        }

        if (is_string($dimension)) {
            if (!empty($extraction)) {
                $builder = new ExtractionBuilder();
                call_user_func($extraction, $builder);

                $extraction = $builder->getExtraction();
            } else {
                $extraction = null;
            }

            $this->addDimension(
                new Dimension($dimension, ($as ?: $dimension), $outputType, $extraction)
            );
        } else {
            $this->addDimension($dimension);
        }

        return $this;
    }

    /**
     *
     * @param string      $lookupFunction
     * @param string      $dimension
     * @param string      $as
     * @param bool|string $replaceMissingValue
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