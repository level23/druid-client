<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputSources\LocalInputSource;

class LocalInputSourceTest extends TestCase
{
    public function testLocalInputSourceFiles(): void
    {
        $local = new LocalInputSource([
            '/path/to/file1.json',
            '/path/to/file2.json',
        ]);

        $this->assertEquals([
            'type'  => 'local',
            'files' => [
                '/path/to/file1.json',
                '/path/to/file2.json',
            ],
        ], $local->toArray());
    }

    public function testLocalInputSourceFilter(): void
    {
        $local = new LocalInputSource([], '/path/to/dir', '*.json');

        $this->assertEquals([
            'type'    => 'local',
            'baseDir' => '/path/to/dir',
            'filter'  => '*.json',
        ], $local->toArray());
    }

    public function testLocalInputSourceCombined(): void
    {
        $local = new LocalInputSource([
            '/path/to/file1.json',
            '/path/to/file2.json',
        ], '/path/to/dir', '*.json');

        $this->assertEquals([
            'type'    => 'local',
            'files'   => [
                '/path/to/file1.json',
                '/path/to/file2.json',
            ],
            'baseDir' => '/path/to/dir',
            'filter'  => '*.json',
        ], $local->toArray());
    }

    public function testLocalInputSourceWithoutData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify either $baseDir or $files');

        new LocalInputSource();
    }

    public function testLocalInputSourceBaseDirWithoutFilter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify both $filter and $baseDir to make use of these!');

        new LocalInputSource([], '/path/to/dir/', null);
    }

    public function testLocalInputSourceFilterWithoutBaseDir(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify both $filter and $baseDir to make use of these!');

        new LocalInputSource(['/path/to/list.json'], null, '*.json');
    }
}