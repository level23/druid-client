<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\InputSources\DruidInputSource;

class DruidInputSourceTest extends TestCase
{
    public function testDruidInputSource(): void
    {
        $interval    = new Interval('12-04-2019', '15-04-2019');
        $inputSource = new DruidInputSource('test', $interval);

        $this->assertEquals([
            'type'       => 'druid',
            'dataSource' => 'test',
            'interval'   => $interval->getInterval(),
        ], $inputSource->toArray());
    }
}
