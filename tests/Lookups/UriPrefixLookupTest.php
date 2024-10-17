<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Lookups;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Lookups\UriPrefixLookup;
use Level23\Druid\Lookups\ParseSpecs\CsvParseSpec;

class UriPrefixLookupTest extends TestCase
{
    public function testLookup(): void
    {
        $parseSpec = new CsvParseSpec(
            ['key', 'value'], 'key', 'value'
        );

        $lookup = new UriPrefixLookup(
            $parseSpec,
            's3://bucket/some/key/prefix/',
            'renames-[0-9]*\.gz',
        );

        $this->assertEquals([
            'type'                => 'cachedNamespace',
            'extractionNamespace' => [
                'type'               => 'uri',
                'uriPrefix'          => 's3://bucket/some/key/prefix/',
                'fileRegex'          => 'renames-[0-9]*\.gz',
                'namespaceParseSpec' => $parseSpec->toArray(),
            ],
            'injective'           => false,
            'firstCacheTimeout'   => 0,
        ], $lookup->toArray());

        $lookup = new UriPrefixLookup(
            $parseSpec,
            '/mount/files/',
            null,
            'PT15M',
            15,
            true,
            6000
        );

        $this->assertEquals([
            'type'                => 'cachedNamespace',
            'extractionNamespace' => [
                'type'               => 'uri',
                'uriPrefix'          => '/mount/files/',
                'namespaceParseSpec' => $parseSpec->toArray(),
                'pollPeriod'         => 'PT15M',
                'maxHeapPercentage'  => 15,
            ],
            'injective'           => true,
            'firstCacheTimeout'   => 6000,
        ], $lookup->toArray());
    }
}
