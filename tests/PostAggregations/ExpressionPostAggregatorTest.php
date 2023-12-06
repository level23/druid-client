<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\PostAggregations\ExpressionPostAggregator;

class ExpressionPostAggregatorTest extends TestCase
{
    public function testAggregator(): void
    {
        $aggregator = new ExpressionPostAggregator(
            'mySum',
            'field1 + field2',
            'numericFirst',
            DataType::FLOAT
        );

        $this->assertEquals([
            'type'       => 'expression',
            'name'       => 'mySum',
            'expression' => 'field1 + field2',
            'ordering'   => 'numericFirst',
            'outputType' => DataType::FLOAT->value,
        ], $aggregator->toArray());
    }

    public function testWithNull(): void
    {
        $aggregator = new ExpressionPostAggregator(
            'mySub',
            'field1 - field2',
        );

        $this->assertEquals([
            'type'       => 'expression',
            'name'       => 'mySub',
            'expression' => 'field1 - field2',
        ], $aggregator->toArray());
    }

    public function testWithComplexType(): void
    {
        $aggregator = new ExpressionPostAggregator(
            'mySub',
            'field1 - field2',
            null,
            'COMPLEX<json>'
        );

        $this->assertEquals([
            'type'       => 'expression',
            'name'       => 'mySub',
            'expression' => 'field1 - field2',
            'outputType' => 'COMPLEX<json>',
        ], $aggregator->toArray());
    }
}
