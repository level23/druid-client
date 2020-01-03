<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\Types\NullHandling;
use Level23\Druid\Extractions\StringFormatExtraction;

class StringFormatExtractionTest extends TestCase
{
    /**
     * @testWith ["[%s]", "nullString", false]
     *           ["%02d", "emptyString", false]
     *           ["%02d", "returnNull", false]
     *           ["%02d", "wrong", true]
     *
     * @param string $sprintf
     * @param string $nullHandling
     * @param bool   $expectException
     */
    public function testExtraction(string $sprintf, string $nullHandling, bool $expectException)
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('The given NullHandling value is invalid');
        }

        $extraction = new StringFormatExtraction($sprintf, $nullHandling);

        $this->assertEquals([
            'name'         => 'stringFormat',
            'format'       => $sprintf,
            'nullHandling' => $nullHandling,
        ], $extraction->toArray());
    }

    public function testDefaults()
    {
        $extraction = new StringFormatExtraction('[%s]');

        $this->assertEquals([
            'name'         => 'stringFormat',
            'format'       => '[%s]',
            'nullHandling' => NullHandling::NULL_STRING,
        ], $extraction->toArray());
    }
}
