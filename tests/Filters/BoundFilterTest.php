<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Filters\BoundFilter;
use Level23\Druid\Types\SortingOrder;
use tests\TestCase;

class BoundFilterTest extends TestCase
{
    public function dataProvider(): array
    {
        $fields      = ['name'];
        $values      = ['18', 'foo'];
        $operators   = ['>', '>=', '<', '<='];
        $orderings   = SortingOrder::values();
        $orderings[] = null;

        $result = [];

        foreach ($fields as $dimension) {
            foreach ($values as $value) {
                foreach ($operators as $operator) {
                    foreach ($orderings as $ordering) {
                        $result[] = [$dimension, $operator, $value, $ordering];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string                                 $dimension
     * @param string                                 $operator
     * @param string                                 $value
     * @param \Level23\Druid\Types\SortingOrder|null $ordering
     */
    public function testFilter(string $dimension, string $operator, string $value, SortingOrder $ordering = null)
    {
        $filter = new BoundFilter($dimension, $operator, $value, $ordering);

        $expected = [
            'type'      => 'bound',
            'dimension' => $dimension,
        ];
        switch ($operator) {
            case '>=':
                $expected['lower']       = $value;
                $expected['lowerStrict'] = false;
                break;
            case '>':
                $expected['lower']       = $value;
                $expected['lowerStrict'] = true;
                break;
            case '<=':
                $expected['upper']       = $value;
                $expected['upperStrict'] = false;
                break;
            case '<':
                $expected['upper']       = $value;
                $expected['upperStrict'] = true;
                break;
        }

        $expected['ordering'] = ($ordering ?: (is_numeric($value) ? SortingOrder::NUMERIC() : SortingOrder::LEXICOGRAPHIC()))->getValue();

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testInvalidOperator()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operator given');

        new BoundFilter('age', 'is', '18');
    }

    public function testExtractionFunction()
    {
        $extractionFunction = new LookupExtraction(
            'age_by_member', false
        );

        $filter = new BoundFilter(
            'member_id',
            '>',
            '18',
            SortingOrder::ALPHANUMERIC(),
            $extractionFunction
        );

        $this->assertEquals([
            'type'         => 'bound',
            'dimension'    => 'member_id',
            'ordering'     => 'alphanumeric',
            'lower'        => 18,
            'lowerStrict'  => true,
            'extractionFn' => $extractionFunction->toArray(),
        ], $filter->toArray()
        );
    }
}