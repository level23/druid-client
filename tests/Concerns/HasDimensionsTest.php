<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Mockery;
use ValueError;
use ArrayObject;
use Mockery\MockInterface;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Mockery\LegacyMockInterface;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Dimensions\LookupDimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Dimensions\ListFilteredDimension;
use Level23\Druid\Dimensions\RegexFilteredDimension;
use Level23\Druid\Dimensions\PrefixFilteredDimension;

class HasDimensionsTest extends TestCase
{
    protected QueryBuilder|MockInterface|LegacyMockInterface $builder;

    public function setUp(): void
    {
        $client = new DruidClient([]);

        $this->builder = Mockery::mock(QueryBuilder::class, [$client, 'test', 'all']);
        $this->builder->makePartial();
    }

    /**
     * Our data sets for our select method.
     *
     * @return array<array<array<string,string>|bool|array<int|string,string|null|array<int|string,string|null>|Dimension|\ArrayObject<string,string>>>>
     */
    public static function selectDataProvider(): array
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
            [['country_iso', 'country', DataType::LONG->value], $expectedLong],
        ];
    }

    /**
     * Test our select method with various types.
     *
     * @dataProvider selectDataProvider
     *
     * @param array<int,string|DimensionInterface|ArrayObject<string,string>|array<int|string,string|null>> $parameters
     * @param array<string,string>                                                                          $expectedResult
     * @param bool                                                                                          $expectException
     */
    public function testSelect(array $parameters, array $expectedResult, bool $expectException = false): void
    {
        if ($expectException) {
            $this->expectException(ValueError::class);
        }

        $response = null;
        $callback = [$this->builder, 'select'];
        if (is_callable($callback)) {
            $response = call_user_func_array($callback, $parameters);
        }

        $this->assertEquals($response, $this->builder);

        $collection = $this->builder->getDimensions();

        $this->assertIsArray($collection);
        $this->assertCount(1, $collection);

        /** @var \Level23\Druid\Dimensions\Dimension $dimension */
        $dimension = $collection[0];

        $this->assertInstanceOf(DimensionInterface::class, $dimension);

        $this->assertEquals($expectedResult, $dimension->toArray());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLookup(): void
    {
        Mockery::mock('overload:' . LookupDimension::class, DimensionInterface::class)
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

        $this->builder->shouldAllowMockingProtectedMethods()
            ->shouldReceive('addDimension')
            ->once();

        $response = $this->builder->lookup('full_name', 'name', 'display_name', 'John Doe');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInlineLookup(): void
    {
        $departments = [
            1 => 'Administration',
            2 => 'Marketing',
            3 => 'Shipping',
            4 => 'IT',
            5 => 'Accounting',
            6 => 'Finance',
        ];

        Mockery::mock('overload:' . LookupDimension::class, DimensionInterface::class)
            ->shouldReceive('__construct')
            ->andReturnUsing(function (
                $dimension,
                $map,
                $as,
                $replaceMissingValue,
                $isOneToOne
            ) use ($departments) {
                $this->assertEquals('department_id', $dimension);
                $this->assertEquals('department', $as);
                $this->assertEquals($departments, $map);
                $this->assertTrue($isOneToOne);

                return true;
            })
            ->once();

        $this->builder->shouldAllowMockingProtectedMethods()
            ->shouldReceive('addDimension')
            ->once();

        $response = $this->builder->inlineLookup(
            $departments,
            'department_id',
            'department',
            'Unknown',
            true
        );

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLookupDefaults(): void
    {
        Mockery::mock('overload:' . LookupDimension::class, DimensionInterface::class)
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
                $this->assertFalse($replaceMissingValue);
            })
            ->once();

        $this->builder->shouldAllowMockingProtectedMethods()
            ->shouldReceive('addDimension')
            ->once();

        $result = $this->builder->lookup('full_name', 'name');

        $this->assertEquals($result, $this->builder);
    }

    /**
     * @testWith ["string", true]
     *           ["long", true]
     *           ["double", false]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMultiValueListSelect(string $outputType, bool $isWhitelist): void
    {
        $dimensionName = 'myMultiValDimension';
        $values        = ['a', 'b', 'c'];
        $as            = 'baz';

        Mockery::mock('overload:' . Dimension::class)
            ->shouldReceive('__construct')
            ->with($dimensionName, $as, $outputType)
            ->once();

        Mockery::mock('overload:' . ListFilteredDimension::class, DimensionInterface::class)
            ->shouldReceive('__construct')
            ->with(new IsInstanceOf(Dimension::class), $values, $isWhitelist)
            ->once();

        $result = $this->builder->multiValueListSelect($dimensionName, $values, $as, $outputType, $isWhitelist);

        $this->assertEquals($result, $this->builder);
    }

    /**
     * @testWith ["string"]
     *           ["long"]
     *           ["float"]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMultiValueRegexSelect(string $outputType): void
    {
        $dimensionName = 'myMultiValDimension';
        $regex         = "^(a|b)$";
        $as            = 'baz';

        Mockery::mock('overload:' . Dimension::class)
            ->shouldReceive('__construct')
            ->with($dimensionName, $as, $outputType)
            ->once();

        Mockery::mock('overload:' . RegexFilteredDimension::class, DimensionInterface::class)
            ->shouldReceive('__construct')
            ->with(new IsInstanceOf(Dimension::class), $regex)
            ->once();

        $result = $this->builder->multiValueRegexSelect($dimensionName, $regex, $as, $outputType);

        $this->assertEquals($result, $this->builder);
    }

    /**
     * @testWith ["string"]
     *           ["long"]
     *           ["float"]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMultiValuePrefixSelect(string $outputType): void
    {
        $dimensionName = 'myMultiValDimension';
        $prefix        = "my";
        $as            = 'baz';

        Mockery::mock('overload:' . Dimension::class)
            ->shouldReceive('__construct')
            ->with($dimensionName, $as, $outputType)
            ->once();

        Mockery::mock('overload:' . PrefixFilteredDimension::class, DimensionInterface::class)
            ->shouldReceive('__construct')
            ->with(new IsInstanceOf(Dimension::class), $prefix)
            ->once();

        $result = $this->builder->multiValuePrefixSelect($dimensionName, $prefix, $as, $outputType);

        $this->assertEquals($result, $this->builder);
    }
}
