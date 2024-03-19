<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Dimensions;

use ValueError;
use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\Dimension;

class DimensionTest extends TestCase
{
    /**
     * @return array<array<string|null|bool>>
     */
    public static function dataProvider(): array
    {
        return [
            ["name", "full_name", "string", false],
            ["name", "__time", "string", false],
            ["name", null, "STRING", false],
            ["name", "full_name", "double", true],
            ["name", "full_name", "whatever", false, true],
            ["name", "full_name", "", false],
            ["name", "full_name", "", false],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string      $dimension
     * @param string|null $outputName
     * @param string      $type
     * @param bool        $expectException
     * @param bool        $valueError
     */
    public function testDimension(
        string $dimension,
        ?string $outputName,
        string $type,
        bool $expectException,
        bool $valueError = false
    ): void {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        if ($valueError) {
            $this->expectException(ValueError::class);
            $this->expectExceptionMessage('is not a valid backing value for enum Level23\Druid\Types\DataType');
        }

        if (!empty($type)) {
            $dimensionObj = new Dimension($dimension, $outputName, $type);
        } else {
            $dimensionObj = new Dimension($dimension, $outputName);
        }
        $expected = [
            'type'       => 'default',
            'dimension'  => $dimension,
            'outputName' => ($outputName ?: $dimension),
            'outputType' => strtolower($type ?: "string"),
        ];

        $this->assertEquals($expected, $dimensionObj->toArray());

        $this->assertEquals(($outputName ?: $dimension), $dimensionObj->getOutputName());
        $this->assertEquals($dimension, $dimensionObj->getDimension());
    }

    public function testDimensionDefaults(): void
    {
        $dimension    = 'countryIso';
        $outputName   = 'country';
        $dimensionObj = new Dimension($dimension, $outputName, '');

        $expected = [
            'type'       => 'default',
            'dimension'  => $dimension,
            'outputName' => $outputName,
            'outputType' => "string",
        ];

        $this->assertEquals($expected, $dimensionObj->toArray());

        $this->assertEquals($outputName, $dimensionObj->getOutputName());
        $this->assertEquals($dimension, $dimensionObj->getDimension());
    }
}
