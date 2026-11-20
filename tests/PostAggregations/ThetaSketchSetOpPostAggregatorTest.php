<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use ValueError;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\ThetaSketchFunction;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;
use Level23\Druid\PostAggregations\ThetaSketchSetOpPostAggregator;
use PHPUnit\Framework\Attributes\DataProvider;

class ThetaSketchSetOpPostAggregatorTest extends TestCase
{
    /**
     * @return array<string,array<int,string|ThetaSketchFunction|int|null>>
     */
    public static function dataProvider(): array
    {
        return [
            'enum union'       => [ThetaSketchFunction::UNION, null, 'UNION'],
            'string intersect' => ['intersect', null, 'INTERSECT'],
            'string not'       => ['NOT', 32768, 'NOT'],
        ];
    }

    #[DataProvider('dataProvider')]
    public function testAggregator(string|ThetaSketchFunction $function, ?int $size, string $expectedFunction): void
    {
        $fields = new PostAggregationCollection(
            new FieldAccessPostAggregator('step1', 'step1'),
            new FieldAccessPostAggregator('step2', 'step2')
        );

        $aggregator = new ThetaSketchSetOpPostAggregator('both', $function, $fields, $size);

        $expected = [
            'type'   => 'thetaSketchSetOp',
            'name'   => 'both',
            'func'   => $expectedFunction,
            'fields' => $fields->toArray(),
        ];

        if ($size !== null) {
            $expected['size'] = $size;
        }

        $this->assertEquals($expected, $aggregator->toArray());
    }

    public function testInvalidFunction(): void
    {
        $this->expectException(ValueError::class);

        new ThetaSketchSetOpPostAggregator('both', 'xor', new PostAggregationCollection());
    }
}
