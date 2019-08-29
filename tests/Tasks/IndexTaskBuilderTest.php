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

class IndexTaskBuilderTest extends TestCase
{
    /**
     * @testWith ["Level23\\Druid\\Firehoses\\IngestSegmentFirehose"]
     *           []
     * @param string|null $firehoseType
     *
     * @throws \ReflectionException
     */
    public function testConstructor($firehoseType = null)
    {
        $client     = new DruidClient([]);
        $dataSource = 'people';
        $builder    = new IndexTaskBuilder($client, $dataSource, $firehoseType);

        $this->assertEquals(
            false,
            $this->getProperty($builder, 'append')
        );
        $this->assertEquals($builder, $builder->append());

        $this->assertEquals(
            true,
            $this->getProperty($builder, 'append')
        );

        $this->assertEquals(
            false,
            $this->getProperty($builder, 'rollup')
        );
        $this->assertEquals($builder, $builder->rollup());
        $this->assertEquals(
            true,
            $this->getProperty($builder, 'rollup')
        );

        $this->assertEquals(
            $dataSource,
            $this->getProperty($builder, 'dataSource')
        );

        $this->assertEquals(
            $client,
            $this->getProperty($builder, 'client')
        );

        $this->assertEquals(
            $firehoseType,
            $this->getProperty($builder, 'firehoseType')
        );

        $this->assertEquals(
            UniformGranularity::class,
            $this->getProperty($builder, 'granularityType')
        );
        $this->assertEquals($builder, $builder->arbitraryGranularity());
        $this->assertEquals(
            ArbitraryGranularity::class,
            $this->getProperty($builder, 'granularityType')
        );

        $this->assertEquals($builder, $builder->uniformGranularity());
        $this->assertEquals(
            UniformGranularity::class,
            $this->getProperty($builder, 'granularityType')
        );
    }

    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $withTransform
     *
     * @throws \ReflectionException
     */
    public function testTransformBuilder(bool $withTransform)
    {
        $client     = new DruidClient([]);
        $dataSource = 'animals';
        $builder    = new IndexTaskBuilder($client, $dataSource);

        $counter  = 0;
        $function = function ($builder) use (&$counter, $withTransform) {
            $this->assertInstanceOf(TransformBuilder::class, $builder);
            $counter++;

            if ($withTransform) {
                /** @var TransformBuilder $builder */
                $builder->transform('concat(foo, bar)', 'fooBar');
            }
        };

        $response = $builder->transform($function);

        $this->assertEquals($builder, $response);

        $this->assertEquals(1, $counter);

        if ($withTransform) {
            $transformSpec = $this->getProperty($builder, 'transformSpec');
            $this->assertInstanceOf(
                TransformSpec::class,
                $transformSpec
            );

            $this->assertEquals([
                'transforms' => [
                    [
                        'type'       => 'expression',
                        'name'       => 'fooBar',
                        'expression' => 'concat(foo, bar)',
                    ],
                ],
            ], $transformSpec->toArray());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testDimension()
    {
        $client     = new DruidClient([]);
        $dataSource = 'aliens';
        $builder    = new IndexTaskBuilder($client, $dataSource);

        $builder->dimension('name', 'STRING');
        $builder->dimension('age', 'LoNg');

        $this->assertEquals([
            ['name' => 'name', 'type' => 'string'],
            ['name' => 'age', 'type' => 'long'],
        ], $this->getProperty($builder, 'dimensions'));
    }

    public function buildTaskDataProvider(): array
    {
        return [
            ["day", "week", new Interval("12-02-2019/13-02-2019"), IngestSegmentFirehose::class],
            ["day", "hour", new Interval("12-02-2019/13-02-2019"), IngestSegmentFirehose::class],
            ["day", "day", new Interval("12-02-2019/13-02-2019"), null],

        ];
    }

    /**
     * @param string                           $queryGranularity
     * @param string                           $segmentGranularity
     * @param \Level23\Druid\Interval\Interval $interval
     * @param string|null                      $firehoseType
     *
     * @throws \ReflectionException
     * @throws \Exception
     * @dataProvider        buildTaskDataProvider
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBuildTask(
        string $queryGranularity,
        string $segmentGranularity,
        Interval $interval,
        ?string $firehoseType
    ) {
        $context    = [];
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource, $firehoseType]);
        $builder->makePartial();

        $this->assertEquals(
            $firehoseType,
            $this->getProperty($builder, 'firehoseType')
        );

        switch ($firehoseType) {
            case IngestSegmentFirehose::class:

                $builder->shouldAllowMockingProtectedMethods()
                    ->shouldReceive('validateInterval')
                    ->once()
                    ->with($dataSource, new IsAnything());

                break;

            default:
                $this->expectException(InvalidArgumentException::class);
                $this->expectExceptionMessage('No firehose known.');
                break;
        }

        $builder->queryGranularity($queryGranularity);
        $builder->segmentGranularity($segmentGranularity);
        $builder->interval($interval->getStart(), $interval->getStop());

        if ($firehoseType) {
            $mock = new Mockery\Generator\MockConfigurationBuilder();
            $mock->setInstanceMock(true);
            $mock->setName(IndexTask::class);
            $mock->addTarget(TaskInterface::class);

            Mockery::mock($mock)
                ->shouldReceive('__construct')
                ->once()
                ->with(
                    $dataSource,
                    new IsInstanceOf(FirehoseInterface::class),
                    new IsInstanceOf(GranularityInterface::class),
                    null,
                    null,
                    new IsInstanceOf(TaskContext::class),
                    new IsInstanceOf(AggregationCollection::class),
                    new IsArray()
                );
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $builder->shouldAllowMockingProtectedMethods()->buildTask($context);
    }

    public function testBuildTaskWithoutQueryGranularity()
    {
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify a queryGranularity value!');

        /** @noinspection PhpUndefinedMethodInspection */
        $builder->shouldAllowMockingProtectedMethods()->buildTask([]);
    }

    public function testBuildTaskWithoutInterval()
    {
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify an interval!');

        $builder->queryGranularity('day');

        /** @noinspection PhpUndefinedMethodInspection */
        $builder->shouldAllowMockingProtectedMethods()->buildTask([]);
    }

    /**
     * @throws \Exception
     */
    public function testBuildTaskWithoutSegmentGranularity()
    {
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify a segmentGranularity value!');

        $builder->queryGranularity('day');
        $builder->interval('12-02-2019', '13-02-2019');
        $builder->uniformGranularity();

        /** @noinspection PhpUndefinedMethodInspection */
        $builder->shouldAllowMockingProtectedMethods()->buildTask([]);
    }

    /**
     * @testWith ["Level23\\Druid\\Granularities\\UniformGranularity"]
     *           ["Level23\\Druid\\Granularities\\ArbitraryGranularity"]
     *
     * @param string $granularityType
     *
     * @throws \Exception
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     */
    public function testBuildTaskGranularityObject(string $granularityType)
    {
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        $builder->queryGranularity('day');
        $builder->segmentGranularity('week');
        $builder->interval('12-02-2019', '13-02-2019');

        if ($granularityType == ArbitraryGranularity::class) {
            $builder->arbitraryGranularity();

            $this->getGranularityMock(ArbitraryGranularity::class)
                ->shouldReceive('__construct')
                ->with(
                    'day',
                    false,
                    new IsInstanceOf(IntervalCollection::class)
                );
        } else {
            $builder->uniformGranularity();

            $this->getGranularityMock(UniformGranularity::class)
                ->shouldReceive('__construct')
                ->with(
                    'week',
                    'day',
                    false,
                    new IsInstanceOf(IntervalCollection::class)
                );
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('firehose');

        /** @noinspection PhpUndefinedMethodInspection */
        $builder->shouldAllowMockingProtectedMethods()->buildTask([]);
    }

    /**
     * @param string $class
     *
     * @return \Mockery\Generator\MockConfigurationBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getGranularityMock(string $class)
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        $builder->addTarget(GranularityInterface::class);

        return Mockery::mock($builder);
    }
}