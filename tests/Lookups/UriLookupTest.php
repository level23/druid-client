<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Lookups;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Lookups\UriLookup;
use Level23\Druid\Lookups\ParseSpecs\CsvParseSpec;
use Level23\Druid\Lookups\ParseSpecs\SimpleJsonParseSpec;

class UriLookupTest extends TestCase
{
    public function testLookup(): void
    {
        $parseSpec = new SimpleJsonParseSpec();

        $lookup = new UriLookup(
            $parseSpec,
            '/mount/files/data.json',
        );

        $this->assertEquals([
            'type'                => 'cachedNamespace',
            'extractionNamespace' => [
                'type'               => 'uri',
                'uri'                => '/mount/files/data.json',
                'namespaceParseSpec' => $parseSpec->toArray(),
            ],
            'injective'           => false,
            'firstCacheTimeout'   => 0,
        ], $lookup->toArray());

        $parseSpec = new CsvParseSpec(
            ['key', 'value'], 'key', 'value'
        );

        $lookup = new UriLookup(
            $parseSpec,
            "s3://bucket/some/key/prefix/renames-0003.gz",
            'PT15M',
            15,
            true,
            6000
        );

        $this->assertEquals([
            'type'                => 'cachedNamespace',
            'extractionNamespace' => [
                'type'               => 'uri',
                'uri'                => 's3://bucket/some/key/prefix/renames-0003.gz',
                'namespaceParseSpec' => $parseSpec->toArray(),
                'pollPeriod'         => 'PT15M',
                'maxHeapPercentage'  => 15,
            ],
            'injective'           => true,
            'firstCacheTimeout'   => 6000,
        ], $lookup->toArray());
    }
}
