<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\ExpressionFilter;

class ExpressionFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $filter = new ExpressionFilter('(expression == 1)');

        $this->assertEquals([
            'type' => 'expression',
            'expression' => '(expression == 1)'
        ], $filter->toArray());
    }
}
