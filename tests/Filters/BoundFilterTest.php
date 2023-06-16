<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use ValueError;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Filters\BoundFilter;
use Level23\Druid\Extractions\LookupExtraction;

class BoundFilterTest extends TestCase
{
    /**
     * @return array<array<string|null|bool|float>>
     */
    public static function dataProvider(): array
    {
        $fields      = ['name'];
        $values      = ['18', 'foo'];
        $operators   = ['>', '>=', '<', '<='];
        $orderings   = SortingOrder::cases();
        $orderings[] = null;

        $result = [];

        foreach ($fields as $dimension) {
            foreach ($values as $value) {
                foreach ($operators as $operator) {
                    foreach ($orderings as $ordering) {
                        $result[] = [$dimension, $operator, $value, $ordering?->value];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string      $dimension
     * @param string      $operator
     * @param string      $value
     * @param string|null $ordering
     */
    public function testFilter(string $dimension, string $operator, string $value, string $ordering = null): void
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

        $expected['ordering'] = $ordering ?: (
            is_numeric($value)
                ? SortingOrder::NUMERIC->value
                : SortingOrder::LEXICOGRAPHIC->value
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testInvalidOperator(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('"is" is not a valid backing value for enum Level23\Druid\Types\BoundOperator');

        new BoundFilter('age', 'is', '18');
    }

    public function testExtractionFunction(): void
    {
        $extractionFunction = new LookupExtraction(
            'age_by_member', false
        );

        $filter = new BoundFilter(
            'member_id',
            '>',
            '18',
            SortingOrder::ALPHANUMERIC,
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
