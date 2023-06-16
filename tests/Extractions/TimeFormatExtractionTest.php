<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Extractions\TimeFormatExtraction;

class TimeFormatExtractionTest extends TestCase
{
    /**
     * @return array<array<string|bool>>
     */
    public static function dataProvider(): array
    {
        return [
            [],
            ['yyyy-MM-dd HH:00:00'],
            ['yyyy-MM-dd HH:00:00', 'fifteen_minute'],
            ['yyyy-MM-dd HH:00:00', 'fifteen_minute', 'fr'],
            ['yyyy-MM-dd HH:00:00', 'fifteen_minute', 'fr', 'America/Montreal'],
            ['yyyy-MM-dd HH:00:00', 'fifteen_minute', 'fr', 'America/Montreal', true],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string|null $format
     * @param string|null $granularity
     * @param string|null $locale
     * @param string|null $timezone
     * @param bool|null   $asMillis
     */
    public function testExtraction(
        string $format = null,
        string $granularity = null,
        string $locale = null,
        string $timezone = null,
        bool $asMillis = null
    ): void {
        $extraction = new TimeFormatExtraction(
            $format,
            $granularity,
            $locale,
            $timezone,
            $asMillis
        );

        $expected = [
            'type'        => 'timeFormat',
            'format'      => $format,
            'granularity' => $granularity,
            'locale'      => $locale,
            'timeZone'    => $timezone,
            'asMillis'    => $asMillis,
        ];

        foreach ($expected as $name => $value) {
            if ($value === null) {
                unset($expected[$name]);
            }
        }

        $this->assertEquals($expected, $extraction->toArray());
    }
}
