<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use Mockery;
use tests\TestCase;
use InvalidArgumentException;
use Hamcrest\Core\IsAnything;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Tasks\IndexTaskBuilder;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\Transforms\TransformBuilder;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Firehoses\IngestSegmentFirehose;
use Level23\Druid\Granularities\UniformGranularity;
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
            [null, null, UniformGranularity::class, null, null, []],
            ["day", null, UniformGranularity::class, null, null, []],
            ["day", new Interval("12-02-2019/13-02-2019"), ArbitraryGranularity::class, null, null, []],
            ["day", new Interval("12-02-2019/13-02-2019"), UniformGranularity::class, null, null, []],
            ["day", new Interval("12-02-2019/13-02-2019"), UniformGranularity::class, "day", null, []],
            [
                "day",
                new Interval("12-02-2019/13-02-2019"),
                UniformGranularity::class,
                "day",
                IngestSegmentFirehose::class,
                [],
            ],
        ];
    }

    /**
     * @param string|null                           $queryGranularity
     * @param \Level23\Druid\Interval\Interval|null $interval
     * @param string                                $granularityType
     * @param string|null                           $segmentGranularity
     * @param string|null                           $firehoseType
     * @param                                       $context
     *
     * @dataProvider        buildTaskDataProvider
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws \Exception
     */
    public function testBuildTask(
        ?string $queryGranularity,
        ?Interval $interval,
        string $granularityType,
        ?string $segmentGranularity,
        ?string $firehoseType,
        $context
    ) {
        $client     = new DruidClient([]);
        $dataSource = 'farmers';
        $builder    = Mockery::mock(IndexTaskBuilder::class, [$client, $dataSource, $firehoseType]);
        $builder->makePartial();

        if (!$queryGranularity || !$interval) {
            $this->expectException(InvalidArgumentException::class);

            /** @noinspection PhpUndefinedMethodInspection */
            $builder->shouldAllowMockingProtectedMethods()->buildTask($context);

            return;
        }

        if ($granularityType == ArbitraryGranularity::class) {
            $this->getGranularityMock(ArbitraryGranularity::class)
                ->shouldReceive('__construct')
                ->with(
                    $queryGranularity,
                    false,
                    new IsInstanceOf(IntervalCollection::class)
                );
        } else {
            if (!$segmentGranularity) {
                $this->expectException(InvalidArgumentException::class);

                /** @noinspection PhpUndefinedMethodInspection */
                $builder->shouldAllowMockingProtectedMethods()->buildTask($context);

                return;
            }

            $this->getGranularityMock(UniformGranularity::class)
                ->shouldReceive('__construct')
                ->with(
                    $segmentGranularity,
                    $queryGranularity,
                    false,
                    new IsInstanceOf(IntervalCollection::class)
                );
        }

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
        }

        $builder->queryGranularity($queryGranularity);
        $builder->segmentGranularity($segmentGranularity);
        $builder->interval($interval->getStart(), $interval->getStop());

        /** @noinspection PhpUndefinedMethodInspection */
        $builder->shouldAllowMockingProtectedMethods()->buildTask($context);

        $this->assertTrue(true);
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