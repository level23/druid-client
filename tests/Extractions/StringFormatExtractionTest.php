<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use ValueError;
use Level23\Druid\Tests\TestCase;
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
    public function testExtraction(string $sprintf, string $nullHandling, bool $expectException): void
    {
        if ($expectException) {
            $this->expectException(ValueError::class);
            $this->expectExceptionMessage('"'. $nullHandling.'" is not a valid backing value for enum Level23\Druid\Types\NullHandling');
        }

        $extraction = new StringFormatExtraction($sprintf, $nullHandling);

        $this->assertEquals([
            'name'         => 'stringFormat',
            'format'       => $sprintf,
            'nullHandling' => $nullHandling,
        ], $extraction->toArray());
    }

    public function testDefaults(): void
    {
        $extraction = new StringFormatExtraction('[%s]');

        $this->assertEquals([
            'name'         => 'stringFormat',
            'format'       => '[%s]',
            'nullHandling' => NullHandling::NULL_STRING->value,
        ], $extraction->toArray());
    }
}
