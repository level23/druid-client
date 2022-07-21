<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputSources\GoogleCloudInputSource;

class GoogleCloudInputSourceTest extends TestCase
{
    public function testGoogleCloudInputSourceUris(): void
    {
        $azure = new GoogleCloudInputSource(
            ["gs://foo/bar/file.json", "gs://bar/foo/file2.json"]
        );

        $this->assertEquals([
            'type' => 'google',
            'uris' => ["gs://foo/bar/file.json", "gs://bar/foo/file2.json"],
        ], $azure->toArray());
    }

    public function testGoogleCloudInputSourcePrefixes(): void
    {
        $azure = new GoogleCloudInputSource(
            [],
            ["gs://foo/bar/", "gs://bar/foo/"]
        );

        $this->assertEquals([
            'type'     => 'google',
            'prefixes' => ["gs://foo/bar/", "gs://bar/foo/"],
        ], $azure->toArray());
    }

    public function testGoogleCloudInputSourceObjects(): void
    {
        $azure = new GoogleCloudInputSource(
            [],
            [],
            [
                ["bucket" => "foo", "path" => "foo/file1.json"],
                ["bucket" => "bar", "path" => "bar/file2.json"],
            ]
        );

        $this->assertEquals([
            'type'    => 'google',
            'objects' => [
                ["bucket" => "foo", "path" => "foo/file1.json"],
                ["bucket" => "bar", "path" => "bar/file2.json"],
            ],
        ], $azure->toArray());
    }

    public function testGoogleCloudInputSourceCombined(): void
    {
        $azure = new GoogleCloudInputSource(
            ["gs://foo/bar/file.json", "gs://bar/foo/file2.json"],
            ["gs://foo/bar/", "gs://bar/foo/"],
            [
                ["bucket" => "foo", "path" => "foo/file1.json"],
                ["bucket" => "bar", "path" => "bar/file2.json"],
            ]
        );

        $this->assertEquals([
            'type'     => 'google',
            'uris'     => ["gs://foo/bar/file.json", "gs://bar/foo/file2.json"],
            'prefixes' => ["gs://foo/bar/", "gs://bar/foo/"],
            'objects'  => [
                ["bucket" => "foo", "path" => "foo/file1.json"],
                ["bucket" => "bar", "path" => "bar/file2.json"],
            ],
        ], $azure->toArray());
    }

    public function testWithoutArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectDeprecationMessage('You have to specify either $uris, $prefixes or $objects');

        new GoogleCloudInputSource();
    }
}