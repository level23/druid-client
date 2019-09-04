<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Transforms;

use tests\TestCase;
use Level23\Druid\Transforms\TransformBuilder;
use Level23\Druid\Transforms\ExpressionTransform;

class TransformBuilderTest extends TestCase
{
    public function testTransformBuilder()
    {
        $transform = new ExpressionTransform('concat(foo, bar)', 'fooBar');
        $builder   = new TransformBuilder();
        $response  = $builder->transform('concat(foo, bar)', 'fooBar');

        $this->assertEquals([$transform], $builder->getTransforms());
        $this->assertEquals($builder, $response);
    }
}