<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

use InvalidArgumentException;
use Level23\Druid\Types\DataType;
use Level23\Druid\Extractions\ExtractionInterface;

class Dimension implements DimensionInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var string
     */
    protected $outputType;

    /**
     * @var \Level23\Druid\Extractions\ExtractionInterface|null
     */
    protected $extractionFunction;

    /**
     * Dimension constructor.
     *
     * @param string                   $dimension
     * @param string                   $outputName
     * @param string                   $outputType This can either be "long", "float" or "string"
     * @param ExtractionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        string $outputName = null,
        string $outputType = 'string',
        ExtractionInterface $extractionFunction = null
    ) {
        $this->dimension  = $dimension;
        $this->outputName = $outputName ?: $dimension;

        if (is_string($outputType)) {
            $outputType = strtolower($outputType);
        }
        $outputType = $outputType ?: DataType::STRING;

        if (!in_array($outputType, ['string', 'long', 'float'])) {
            throw new InvalidArgumentException(
                'Incorrect type given: ' . $outputType . '. This can either be "long", "float" or "string"'
            );
        }

        $this->outputType         = $outputType;
        $this->extractionFunction = $extractionFunction;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type'       => ($this->extractionFunction ? 'extraction' : 'default'),
            'dimension'  => $this->dimension,
            'outputType' => $this->outputType,
            'outputName' => $this->outputName,
        ];

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->toArray();
        }

        return $result;
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

    /**
     * @return \Level23\Druid\Extractions\ExtractionInterface|null
     */
    public function getExtractionFunction(): ?ExtractionInterface
    {
        return $this->extractionFunction;
    }
}