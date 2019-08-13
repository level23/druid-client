<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\DruidClient;
use Level23\Druid\QueryBuilder;
use Level23\Druid\Types\DataType;
use tests\TestCase;

class HasDimensionsTest extends TestCase
{
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
            [[new Dimension('browser', 'TheBrowser')], $expected],
            [[new \ArrayObject(['browser' => 'TheBrowser'])], $expected],
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
     */
    public function testSelect(array $parameters, $expectedResult)
    {
        $client = new DruidClient([]);

        /** @var QueryBuilder|\Mockery\MockInterface $builder */
        $builder = \Mockery::mock(QueryBuilder::class, [$client, 'test', 'all']);
        $builder->makePartial();

        $response = null;
        $callback = [$builder, 'select'];
        if (is_callable($callback)) {
            $response = call_user_func_array($callback, $parameters);
        }

        $this->assertEquals($response, $builder);

        $collection = $builder->getDimensions();

        $this->assertIsArray($collection);
        $this->assertEquals(1, count($collection));

        /** @var \Level23\Druid\Dimensions\Dimension $dimension */
        $dimension = $collection[0];

        $this->assertInstanceOf(DimensionInterface::class, $dimension);

        $this->assertEquals($expectedResult, $dimension->getDimension());
    }
}
