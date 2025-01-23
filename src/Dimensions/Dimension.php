<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

use InvalidArgumentException;
use Level23\Druid\Types\DataType;

class Dimension implements DimensionInterface
{
    protected string $dimension;

    protected string $outputName;

    protected DataType $outputType;

    /**
     * Dimension constructor.
     *
     * @param string                   $dimension
     * @param string|null              $outputName
     * @param string|DataType          $outputType This can either be "long", "float" or "string"
     */
    public function __construct(
        string $dimension,
        ?string $outputName = null,
        string|DataType $outputType = DataType::STRING
    ) {
        $this->dimension  = $dimension;
        $this->outputName = $outputName ?: $dimension;

        if( empty($outputType)) {
            $outputType = DataType::STRING;
        } else {
            $outputType = is_string($outputType) ? DataType::from(strtolower($outputType)) : $outputType;
        }

        if (!in_array($outputType, [DataType::STRING, DataType::LONG, DataType::FLOAT])) {
            throw new InvalidArgumentException(
                'Incorrect type given: ' . $outputType->value . '. This can either be "long", "float" or "string"'
            );
        }

        $this->outputType         = $outputType;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array<string,string|array<mixed>>
     */
    public function toArray(): array
    {
        return [
            'type'       => 'default',
            'dimension'  => $this->dimension,
            'outputType' => $this->outputType->value,
            'outputName' => $this->outputName,
        ];
    }

    /**
     * Return the name of the dimension which is selected.
     *
     * @return string
     */
    public function getDimension(): string
    {
        return $this->dimension;
    }

    /**
     * Return the output name of this dimension
     *
     * @return string
     */
    public function getOutputName(): string
    {
        return $this->outputName;
    }
}