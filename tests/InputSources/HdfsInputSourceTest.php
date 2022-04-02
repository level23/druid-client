<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputSources\HdfsInputSource;

class HdfsInputSourceTest extends TestCase
{
    public function testHdfsInputSource(): void
    {
        $hdfs = new HdfsInputSource(["hdfs://namenode_host/foo/bar/", "hdfs://namenode_host/bar/foo"]);

        $this->assertEquals([
            'type'  => 'hdfs',
            'paths' => ["hdfs://namenode_host/foo/bar/", "hdfs://namenode_host/bar/foo"],
        ], $hdfs->toArray());

        $hdfs = new HdfsInputSource("hdfs://namenode_host/foo/bar/,hdfs://namenode_host/bar/foo/*");

        $this->assertEquals([
            'type'  => 'hdfs',
            'paths' => "hdfs://namenode_host/foo/bar/,hdfs://namenode_host/bar/foo/*",
        ], $hdfs->toArray());
    }
}