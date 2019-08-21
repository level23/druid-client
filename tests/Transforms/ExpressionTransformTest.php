<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Transforms;

use tests\TestCase;
use Level23\Druid\Transforms\ExpressionTransform;

class ExpressionTransformTest extends TestCase
{
    public function testTransform()
    {
        $transform = new ExpressionTransform("concat('John', 'Doe')", "name");

        $this->assertEquals([
            'type'       => 'expression',
            'name'       => "name",
            'expression' => "concat('John', 'Doe')",
        ], $transform->toArray());
    }
}