<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use InvalidArgumentException;
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

    public function testDruidInputSourceWithoutInterval(): void
    {
        $inputSource = new DruidInputSource('test');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify the interval which you want to use for your query!');
        $inputSource->toArray();
    }

    /**
     * @throws \Exception
     */
    public function testFilter(): void
    {
        $interval = new Interval('12-04-2019', '15-04-2019');

        $inputSource = new DruidInputSource('test');
        $inputSource->interval('12-04-2019', '15-04-2019');
        $inputSource->where('value', 12.9);
        $inputSource->whereFlags('settings', 12);
        $inputSource->orWhere('status', 'admin');

        $response = $inputSource->toArray();
        $this->assertEquals([
            'type'       => 'druid',
            'dataSource' => 'test',
            'interval'   => $interval->getInterval(),
            'filter'     => [
                'type'   => 'or',
                'fields' => [
                    [
                        'type'   => 'and',
                        'fields' => [
                            [
                                'type'           => 'equals',
                                'column'         => 'value',
                                'matchValueType' => 'double',
                                'matchValue'     => 12.9,
                            ],
                            [
                                'type'       => 'expression',
                                'expression' => 'bitwiseAnd("settings", 12) == 12',
                            ],
                        ],
                    ],
                    [
                        'type'           => 'equals',
                        'column'         => 'status',
                        'matchValueType' => 'string',
                        'matchValue'     => 'admin',
                    ],
                ],
            ],
        ], $response);
    }

    public function testDruidInputSourceWithInterval(): void
    {
        $interval    = new Interval('12-04-2019', '15-04-2019');
        $inputSource = new DruidInputSource('test');

        $inputSource->setInterval($interval);

        $this->assertEquals([
            'type'       => 'druid',
            'dataSource' => 'test',
            'interval'   => $interval->getInterval(),
        ], $inputSource->toArray());
    }
}
