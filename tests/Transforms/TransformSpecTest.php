<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Transforms;

use tests\TestCase;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\Transforms\ExpressionTransform;
use Level23\Druid\Collections\TransformCollection;

class TransformSpecTest extends TestCase
{
    public function testTransformSpec()
    {
        $transforms = new TransformCollection(
            new ExpressionTransform('concat(foo, bar)', 'fooBar')
        );

        $filter = new SelectorFilter('name', 'John');

        $spec = new TransformSpec($transforms, $filter);

        $this->assertEquals([
            'transforms' => $transforms->toArray(),
            'filter'     => $filter->toArray(),
        ], $spec->toArray());
    }

    public function testDefaults()
    {
        $spec = new TransformSpec(new TransformCollection(), null );

        $this->assertEquals([], $spec->toArray());
    }
}