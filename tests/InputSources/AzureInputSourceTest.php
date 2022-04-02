<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputSources\AzureInputSource;

class AzureInputSourceTest extends TestCase
{
    public function testAzureInputSourceUris(): void
    {
        $azure = new AzureInputSource(
            ["azure://container/prefix1/file.json", "azure://container/prefix2/file2.json"]
        );

        $this->assertEquals([
            'type' => 'azure',
            'uris' => ["azure://container/prefix1/file.json", "azure://container/prefix2/file2.json"],
        ], $azure->toArray());
    }

    public function testAzureInputSourcePrefixes(): void
    {
        $azure = new AzureInputSource(
            [],
            ["azure://container/prefix1/", "azure://container/prefix2/"]
        );

        $this->assertEquals([
            'type'     => 'azure',
            'prefixes' => ["azure://container/prefix1/", "azure://container/prefix2/"],
        ], $azure->toArray());
    }

    public function testAzureInputSourceObjects(): void
    {
        $azure = new AzureInputSource(
            [],
            [],
            [
                ["bucket" => "container", "path" => "prefix1/file1.json"],
                ["bucket" => "container", "path" => "prefix1/file2.json"],
            ]
        );

        $this->assertEquals([
            'type'    => 'azure',
            'objects' => [
                ["bucket" => "container", "path" => "prefix1/file1.json"],
                ["bucket" => "container", "path" => "prefix1/file2.json"],
            ],
        ], $azure->toArray());
    }

    public function testAzureInputSourceCombined(): void
    {
        $azure = new AzureInputSource(
            ["azure://container/prefix1/file.json", "azure://container/prefix2/file2.json"],
            ["azure://container/prefix1/", "azure://container/prefix2/"],
            [
                ["bucket" => "container", "path" => "prefix1/file1.json"],
                ["bucket" => "container", "path" => "prefix1/file2.json"],
            ]
        );

        $this->assertEquals([
            'type'     => 'azure',
            'uris'     => ["azure://container/prefix1/file.json", "azure://container/prefix2/file2.json"],
            'prefixes' => ["azure://container/prefix1/", "azure://container/prefix2/"],
            'objects'  => [
                ["bucket" => "container", "path" => "prefix1/file1.json"],
                ["bucket" => "container", "path" => "prefix1/file2.json"],
            ],
        ], $azure->toArray());
    }

    public function testWithoutArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectDeprecationMessage('You have to specify either $uris, $prefixes or $objects');

        new AzureInputSource();
    }
}