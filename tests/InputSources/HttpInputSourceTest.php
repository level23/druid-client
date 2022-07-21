<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputSources\HttpInputSource;

class HttpInputSourceTest extends TestCase
{
    public function testHttpInputSource(): void
    {
        $http = new HttpInputSource(
            ["http://example.com/uri1", "http://example2.com/uri2"]
        );

        $this->assertEquals([
            'type' => 'http',
            'uris' => ["http://example.com/uri1", "http://example2.com/uri2"],
        ], $http->toArray());

        $http = new HttpInputSource(
            ["http://example.com/uri1", "http://example2.com/uri2"],
            "Admin",
            "Passwd"
        );

        $this->assertEquals([
            'type'                       => 'http',
            'uris'                       => ["http://example.com/uri1", "http://example2.com/uri2"],
            'httpAuthenticationUsername' => 'Admin',
            'httpAuthenticationPassword' => 'Passwd',
        ], $http->toArray());

        $http = new HttpInputSource(
            ["http://example.com/uri1", "http://example2.com/uri2"],
            "Admin",
            [
                'type'     => 'environment',
                'variable' => 'HTTP_INPUT_SOURCE_PW',
            ]
        );

        $this->assertEquals([
            'type'                       => 'http',
            'uris'                       => ["http://example.com/uri1", "http://example2.com/uri2"],
            'httpAuthenticationUsername' => 'Admin',
            'httpAuthenticationPassword' => [
                'type'     => 'environment',
                'variable' => 'HTTP_INPUT_SOURCE_PW',
            ],
        ], $http->toArray());
    }
}