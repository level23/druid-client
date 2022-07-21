<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputSources\HttpInputSource;
use Level23\Druid\InputSources\LocalInputSource;
use Level23\Druid\InputSources\CombineInputSource;

class CombineInputSourceTest extends TestCase
{
    public function testCombineInputSource(): void
    {
        $http  = new HttpInputSource(['http://test.com/file.json']);
        $local = new LocalInputSource(['/path/to/file.json']);

        $combine = new CombineInputSource([
            $http,
            $local,
        ]);

        $this->assertEquals([
            'type'      => 'combining',
            'delegates' => [
                $http->toArray(),
                $local->toArray(),
            ],
        ], $combine->toArray());
    }

    public function testCombineInputSourceWithWrongContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only input sources are allowed!');
        // @phpstan-ignore-next-line
        new CombineInputSource([new \DateTime('now')]);
    }
}