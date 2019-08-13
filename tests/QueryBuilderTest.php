<?php
declare(strict_types=1);

namespace tests\Level23\Druid;

use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\DruidClient;
use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\QueryBuilder;
use Level23\Druid\Types\DataType;
use tests\TestCase;

class QueryBuilderTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var \Level23\Druid\QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        $this->client  = new DruidClient([]);
        $this->builder = \Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
    }

    /**
     * Test the wherein
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereIn()
    {
        $in = \Mockery::mock('overload:' . InFilter::class);
        $in->shouldReceive('__construct')
            ->once()
            ->with('country_iso', ['nl', 'be']);

        $this->builder->makePartial();
        $this->builder->shouldReceive('where')
            ->once()
            ->with(new IsInstanceOf(InFilter::class));

        $this->builder->whereIn('country_iso', ['nl', 'be']);
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

        $extraction = new LookupExtraction('countries');

        $expectedLookup = [
            'type'         => 'extraction',
            'dimension'    => 'country_iso',
            'outputName'   => 'country',
            'outputType'   => 'long',
            'extractionFn' => $extraction->getExtractionFunction(),
        ];

        return [
            // give as first and second parameter
            [['browser', 'TheBrowser'], $expected],
            // give as array
            [[['browser' => 'TheBrowser']], $expected],
            [[new Dimension('browser', 'TheBrowser')], $expected],
            [[new \ArrayObject(['browser' => 'TheBrowser'])], $expected],
            [['country_iso', 'country', $extraction, DataType::LONG()], $expectedLookup],
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
        /** @var QueryBuilder|\Mockery\MockInterface $builder */
        $builder = \Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
        $builder->makePartial();

        $response = null;
        $callback = [$builder, 'select'];
        if (is_callable($callback)) {
            $response = call_user_func_array($callback, $parameters);
        }

        $this->assertEquals($response, $builder);

        $collection = $builder->getDimensions();

        $this->assertInstanceOf(DimensionCollection::class, $collection);
        $this->assertEquals(1, count($collection));

        /** @var \Level23\Druid\Dimensions\Dimension $dimension */
        $dimension = $collection[0];

        $this->assertInstanceOf(DimensionInterface::class, $dimension);

        $this->assertEquals($expectedResult, $dimension->getDimension());
    }
}