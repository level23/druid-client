<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use tests\TestCase;
use Level23\Druid\Extractions\TimeFormatExtraction;

class TimeFormatExtractionTest extends TestCase
{
    public function dataProvider(): array
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
    ) {
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
