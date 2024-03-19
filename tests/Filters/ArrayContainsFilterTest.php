<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\ArrayContainsFilter;

class ArrayContainsFilterTest extends TestCase
{
    /**
     * @param string|int|float $value
     * @param string           $type
     * @testWith ["John", "string"]
     *           [12, "long"]
     *           [12.1, "double"]
     */
    public function testFilter(string|int|float $value, string $type): void
    {
        $expected = [
            'type'                  => 'arrayContainsElement',
            'column'                => 'var',
            'elementMatchValue'     => $value,
            'elementMatchValueType' => $type,
        ];

        $filter = new ArrayContainsFilter('var', $value, null,);

        $this->assertEquals($expected, $filter->toArray());
    }
}
