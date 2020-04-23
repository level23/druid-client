<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use InvalidArgumentException;
use Level23\Druid\DruidClient;
use tests\Level23\Druid\TestCase;
use Level23\Druid\Tasks\KillTaskBuilder;

class KillTaskBuilderTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testKillTaskBuilder()
    {
        $client  = new DruidClient([]);
        $builder = new KillTaskBuilder($client, "wikipedia");

        $this->assertEquals("wikipedia", $this->getProperty($builder, "dataSource"));
    }

    /**
     * @testWith [null]
     *           ["task-1337"]
     * @param string|null $taskId
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testBuildTask(string $taskId = null)
    {
        $client  = new DruidClient([]);
        $builder = new KillTaskBuilder($client, "wikipedia");

        $expected = [
            'type'       => 'kill',
            'dataSource' => 'wikipedia',
            'interval'   => '2019-01-10T00:00:00.000Z/2019-01-12T00:00:00.000Z',
        ];
        $builder->interval('10-01-2019/12-01-2019');
        if ($taskId) {
            $builder->taskId($taskId);
            $expected['id'] = $taskId;
        }

        $this->assertEquals($expected, $builder->toArray());
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testBuildTaskWithoutInterval()
    {
        $client  = new DruidClient([]);
        $builder = new KillTaskBuilder($client, "wikipedia");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify an interval!');

        $builder->toArray();
    }
}
