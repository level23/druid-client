<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

use InvalidArgumentException;
use Level23\Druid\Extractions\ExtractionInterface;
use Level23\Druid\Types\DataType;

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
     * @var string|null
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
     * @param string|DataType          $outputType This can either be "long", "float" or "string"
     * @param ExtractionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        string $outputName = null,
        $outputType = "string",
        ExtractionInterface $extractionFunction = null
    ) {
        $this->dimension  = $dimension;
        $this->outputName = $outputName ?: $dimension;

        if (is_string($outputType)) {
            $outputType = strtolower($outputType);
        }
        $outputType = $outputType ?: DataType::STRING();

        if (!in_array($outputType, ["string", "long", "float"])) {
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
    public function getDimension(): array
    {
        $result = [
            'type'       => ($this->extractionFunction ? 'extraction' : 'default'),
            'dimension'  => $this->dimension,
            'outputName' => $this->outputName,
            'outputType' => $this->outputType,
        ];

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->getExtractionFunction();
        }

        return $result;
    }
}