<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use ArrayObject;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Dimensions\LookupDimension;
use Level23\Druid\DruidClient;
use Level23\Druid\Extractions\CascadeExtraction;
use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Extractions\SubstringExtraction;
use Level23\Druid\Extractions\TimeFormatExtraction;
use Level23\Druid\QueryBuilder;
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
            [['browser', 'TheBrowser', 'string'], $expected],
            // give as array
            [[['browser' => 'TheBrowser']], $expected],
            // give as array (simple)
            [[['browser']], $expectedSimple],

            // incorrect output type
            [['browser', 'TheBrowser', 'something'], $expected, true],
            [[new Dimension('browser', 'TheBrowser')], $expected],
            [[new ArrayObject(['browser' => 'TheBrowser'])], $expected],
            [['country_iso', 'country', DataType::LONG()], $expectedLong],
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
            $this->expectException(\InvalidArgumentException::class);
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
    public function testExtractTimeFormat()
    {
        $format = 'yyyy-MM-dd HH:00:00';

        Mockery::mock('overload:' . TimeFormatExtraction::class)
            ->shouldReceive('__construct')
            ->with($format, 'quarter', 'fr', 'Europe/Amsterdam', false)
            ->once();

        Mockery::mock('overload:' . Dimension::class)
            ->shouldReceive('__construct')
            ->andReturnUsing(function ($dimension, $as, $type, $extractionFunction) {
                $this->assertEquals('__time', $dimension);
                $this->assertEquals('datetime', $as);
                $this->assertEquals(DataType::STRING(), $type);
                $this->assertInstanceOf(TimeFormatExtraction::class, $extractionFunction);
            })
            ->once();

        $response = $this->builder->extractTimeFormat(
            '__time',
            $format,
            'datetime',
            'quarter',
            'Europe/Amsterdam',
            'fr',
            false
        );

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExtractTimeFormatDefaults()
    {
        Mockery::mock('overload:' . TimeFormatExtraction::class)
            ->shouldReceive('__construct')
            ->with(null, null, null, null, null)
            ->once();

        Mockery::mock('overload:' . Dimension::class)
            ->shouldReceive('__construct')
            ->andReturnUsing(function ($dimension, $as, $type, $extractionFunction) {
                $this->assertEquals('datetime', $dimension);
                $this->assertEquals('datetime', $as);
                $this->assertEquals(DataType::STRING(), $type);
                $this->assertInstanceOf(TimeFormatExtraction::class, $extractionFunction);
            })
            ->once();

        $response = $this->builder->extractTimeFormat('datetime');

        $this->assertEquals($this->builder, $response);
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExtractLookup()
    {
        Mockery::mock('overload:' . LookupExtraction::class)
            ->shouldReceive('__construct')
            ->with('full_name', true, 'John Doe', false, true)
            ->once();

        Mockery::mock('overload:' . Dimension::class)
            ->shouldReceive('__construct')
            ->andReturnUsing(function ($dimension, $as, $type, $extractionFunction) {
                $this->assertEquals('name', $dimension);
                $this->assertEquals('display_name', $as);
                $this->assertEquals('long', $type);
                $this->assertInstanceOf(LookupExtraction::class, $extractionFunction);
            })
            ->once();

        $response = $this->builder->extractLookup(
            'name',
            'full_name',
            'display_name',
            true,
            'John Doe',
            'long',
            false,
            true
        );

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExtractLookupDefaults()
    {
        Mockery::mock('overload:' . LookupExtraction::class)
            ->shouldReceive('__construct')
            ->with('full_name', false, '', true, null)
            ->once();

        Mockery::mock('overload:' . Dimension::class)
            ->shouldReceive('__construct')
            ->andReturnUsing(function ($dimension, $as, $type, $extractionFunction) {
                $this->assertEquals('name', $dimension);
                $this->assertEquals('name', $as);
                $this->assertEquals('string', $type);
                $this->assertInstanceOf(LookupExtraction::class, $extractionFunction);
            })
            ->once();

        $response = $this->builder->extractLookup('name', 'full_name');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExtractCascade()
    {
        $extractions = [
            new LookupExtraction('company_name'),
            new SubstringExtraction(2, 5),
        ];

        Mockery::mock('overload:' . CascadeExtraction::class)
            ->shouldReceive('__construct')
            ->with($extractions)
            ->once();

        Mockery::mock('overload:' . Dimension::class)
            ->shouldReceive('__construct')
            ->andReturnUsing(function ($dimension, $as, $type, $extractionFunction) {
                $this->assertEquals('company_id', $dimension);
                $this->assertEquals('company', $as);
                $this->assertEquals('long', $type);
                $this->assertInstanceOf(CascadeExtraction::class, $extractionFunction);
            })
            ->once();

        $response = $this->builder->extractCascade('company_id', 'company', $extractions, 'long');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExtractCascadeDefaults()
    {
        Mockery::mock('overload:' . CascadeExtraction::class)
            ->shouldReceive('__construct')
            ->with([])
            ->once();

        Mockery::mock('overload:' . Dimension::class)
            ->shouldReceive('__construct')
            ->andReturnUsing(function ($dimension, $as, $type, $extractionFunction) {
                $this->assertEquals('company_id', $dimension);
                $this->assertEquals('company_id', $as);
                $this->assertEquals('string', $type);
                $this->assertInstanceOf(CascadeExtraction::class, $extractionFunction);
            })
            ->once();

        $response = $this->builder->extractCascade('company_id');

        $this->assertEquals($this->builder, $response);
    }
}
