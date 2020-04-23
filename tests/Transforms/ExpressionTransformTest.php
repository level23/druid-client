<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Transforms;

use Level23\Druid\Tests\TestCase;
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
