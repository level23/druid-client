<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Transforms;

use Level23\Druid\Tests\TestCase;
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
