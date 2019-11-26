<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use Mockery;
use tests\TestCase;
use Hamcrest\Type\IsArray;
use InvalidArgumentException;
use Hamcrest\Core\IsAnything;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Tasks\IndexTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Tasks\KillTaskBuilder;
use Level23\Druid\Tasks\IndexTaskBuilder;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\Transforms\TransformBuilder;
use Level23\Druid\Firehoses\FirehoseInterface;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Firehoses\IngestSegmentFirehose;
use Level23\Druid\Granularities\UniformGranularity;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Granularities\ArbitraryGranularity;
use Level23\Druid\Granularities\GranularityInterface;

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