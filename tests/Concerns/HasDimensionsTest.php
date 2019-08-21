<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use ArrayObject;
use InvalidArgumentException;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Dimensions\LookupDimension;
use Level23\Druid\DruidClient;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Types\DataType;
use Mockery;
use tests\TestCase;

class HasDimensionsTest extends TestCase
{
    /**
     * @var QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface $builder
     */
    protected $builder;

    public function setUp(): void
    {
        $client = new DruidClient([]);

        $this->builder = Mockery::mock(QueryBuilder::class, [$client, 'test', 'all']);
        $this->builder->makePartial();
    }

    /**
     * Our data sets for our select method.
     *
     * @return array
     */
    public function selectDataProvider(): array
    {
        $expected = [
            'type'       => 'default',
            'dimension'  => 'browser',
            'outputName' => 'TheBrowser',
            'outputType' => 'string',
        ];

        $expectedSimple = [
            'type'       => 'default',
            'dimension'  => 'browser',
            'outputName' => 'browser',
            'outputType' => 'string',
        ];

        $expectedLong = [
            'type'       => 'default',
            'dimension'  => 'country_iso',
            'outputName' => 'country',
            'outputType' => 'long',
        ];

        return [
            // give as first and second parameter
            [['browser', 'TheBrowser', null, 'string'], $expected],
            // give as array
            [[['browser' => 'TheBrowser']], $expected],
            // give as array (simple)
            [[['browser']], $expectedSimple],

            // incorrect output type
            [['browser', 'TheBrowser', null, 'something'], $expected, true],
            [[new Dimension('browser', 'TheBrowser')], $expected],
            [[new ArrayObject(['browser' => 'TheBrowser'])], $expected],
            [['country_iso', 'country', null, DataType::LONG()], $expectedLong],
        ];
    }

    /**
     * Test our select method with various types.
     *
     * @dataProvider selectDataProvider
     *
     * @param array $parameters
     * @param array $expectedResult
     * @param bool  $expectException
     */
    public function testSelect(array $parameters, $expectedResult, bool $expectException = false)
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $response = null;
        $callback = [$this->builder, 'select'];
        if (is_callable($callback)) {
            $response = call_user_func_array($callback, $parameters);
        }

        $this->assertEquals($response, $this->builder);

        $collection = $this->builder->getDimensions();

        $this->assertIsArray($collection);
        $this->assertEquals(1, count($collection));

        /** @var \Level23\Druid\Dimensions\Dimension $dimension */
        $dimension = $collection[0];

        $this->assertInstanceOf(DimensionInterface::class, $dimension);

        $this->assertEquals($expectedResult, $dimension->toArray());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLookup()
    {
        Mockery::mock('overload:' . LookupDimension::class)
            ->shouldReceive('__construct')
            ->andReturnUsing(function (
                $dimension,
                $lookupFunction,
                $as,
                $replaceMissingValue
            ) {
                $this->assertEquals('name', $dimension);
                $this->assertEquals('display_name', $as);
                $this->assertEquals('full_name', $lookupFunction);
                $this->assertEquals('John Doe', $replaceMissingValue);
            })
            ->once();

        $response = $this->builder->lookup('full_name', 'name', 'display_name', 'John Doe');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLookupDefaults()
    {
        Mockery::mock('overload:' . LookupDimension::class)
            ->shouldReceive('__construct')
            ->andReturnUsing(function (
                $dimension,
                $lookupFunction,
                $as,
                $replaceMissingValue
            ) {
                $this->assertEquals('name', $dimension);
                $this->assertEquals('name', $as);
                $this->assertEquals('full_name', $lookupFunction);
                $this->assertEquals(false, $replaceMissingValue);
            })
            ->once();

        $this->builder->lookup('full_name', 'name');
    }

    public function testSelectWithExtraction()
    {
        $counter = 0;
        $this->builder->select('user_id', 'username', function (ExtractionBuilder $builder) use (&$counter) {
            $counter++;
            $builder->lookup('user', false);
        });

        $this->assertEquals(1, $counter);
    }
}
