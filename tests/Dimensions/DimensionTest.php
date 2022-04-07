<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Dimensions;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Extractions\RegexExtraction;
use Level23\Druid\Extractions\ExtractionInterface;

class DimensionTest extends TestCase
{
    public function dataProvider(): array
    {
        $extraction = new RegexExtraction("^([a-z]+)$");

        return [
            ["name", "full_name", "string", null, false],
            ["name", "__time", "string", null, false],
            ["name", null, "STRING", null, false],
            ["name", "full_name", "double", null, true],
            ["name", "full_name", "whatever", null, true],
            ["name", "full_name", "whatever", null, true],
            ["name", "full_name", "", null, false],
            ["name", "full_name", "", $extraction, false],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string                   $dimension
     * @param string|null              $outputName
     * @param string                   $type
     * @param ExtractionInterface|null $extractionFunction
     * @param bool                     $expectException
     */
    public function testDimension(
        string $dimension,
        ?string $outputName,
        string $type,
        ?ExtractionInterface $extractionFunction,
        bool $expectException
    ): void {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        if (!empty($type) || $extractionFunction !== null) {
            $dimensionObj = new Dimension($dimension, $outputName, $type, $extractionFunction);
        } else {
            $dimensionObj = new Dimension($dimension, $outputName);
        }
        $expected = [
            'type'       => ($extractionFunction ? 'extraction' : 'default'),
            'dimension'  => $dimension,
            'outputName' => ($outputName ?: $dimension),
            'outputType' => strtolower($type ?: "string"),
        ];

        if ($extractionFunction) {
            $expected['extractionFn'] = $extractionFunction->toArray();
        }

        $this->assertEquals($expected, $dimensionObj->toArray());

        $this->assertEquals(($outputName ?: $dimension), $dimensionObj->getOutputName());
        $this->assertEquals($dimension, $dimensionObj->getDimension());

        $this->assertEquals($extractionFunction, $dimensionObj->getExtractionFunction());
    }
}
