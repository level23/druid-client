<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputSources\S3InputSource;

class S3InputSourceTest extends TestCase
{
    public function testS3InputSourceUris(): void
    {
        $s3 = new S3InputSource(
            ['s3://foo/bar/file.json', 's3://bar/foo/file2.json'],
            [],
            [],
            [
                'accessKeyId'     => 'KLJ78979SDFdS2',
                'secretAccessKey' => 'KLS89s98sKJHKJKJH8721lljkd',
            ]
        );

        $this->assertEquals([
            'type'       => 's3',
            'uris'       => ['s3://foo/bar/file.json', 's3://bar/foo/file2.json'],
            'properties' => [
                'accessKeyId'     => 'KLJ78979SDFdS2',
                'secretAccessKey' => 'KLS89s98sKJHKJKJH8721lljkd',
            ],
        ], $s3->toArray());
    }

    public function testS3InputSourcePrefixes(): void
    {
        $s3 = new S3InputSource(
            [],
            ['s3://foo/bar/', 's3://bar/foo/']
        );

        $this->assertEquals([
            'type'     => 's3',
            'prefixes' => ['s3://foo/bar/', 's3://bar/foo/'],
        ], $s3->toArray());
    }

    public function testS3InputSourceObjects(): void
    {
        $s3 = new S3InputSource(
            [],
            [],
            [
                ['bucket' => 'foo', 'path' => 'bar/file1.json'],
                ['bucket' => 'bar', 'path' => 'foo/file2.json'],
            ]
        );

        $this->assertEquals([
            'type'    => 's3',
            'objects' => [
                ['bucket' => 'foo', 'path' => 'bar/file1.json'],
                ['bucket' => 'bar', 'path' => 'foo/file2.json'],
            ],
        ], $s3->toArray());
    }

    public function testS3InputSourceCombined(): void
    {
        $s3 = new S3InputSource(
            ['s3://foo/bar/file.json', 's3://bar/foo/file2.json'],
            ['s3://foo/bar/', 's3://bar/foo/'],
            [
                ['bucket' => 'foo', 'path' => 'bar/file1.json'],
                ['bucket' => 'bar', 'path' => 'foo/file2.json'],
            ]
        );

        $this->assertEquals([
            'type'     => 's3',
            'uris'     => ['s3://foo/bar/file.json', 's3://bar/foo/file2.json'],
            'prefixes' => ['s3://foo/bar/', 's3://bar/foo/'],
            'objects'  => [
                ['bucket' => 'foo', 'path' => 'bar/file1.json'],
                ['bucket' => 'bar', 'path' => 'foo/file2.json'],
            ],
        ], $s3->toArray());
    }

    public function testWithoutArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectDeprecationMessage('You have to specify either $uris, $prefixes or $objects');

        new S3InputSource();
    }
}