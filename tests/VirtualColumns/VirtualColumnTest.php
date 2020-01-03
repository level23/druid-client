<?php
declare(strict_types=1);

namespace tests\Level23\Druid\VirtualColumns;

use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\VirtualColumns\VirtualColumn;

class VirtualColumnTest extends TestCase
{
    public function testVirtualColumn()
    {
        $column = new VirtualColumn('if(mccmnc > 0, country_iso, "")', 'country_iso', 'string');

        $this->assertEquals([
            'type'       => 'expression',
            'name'       => 'country_iso',
            'expression' => 'if(mccmnc > 0, country_iso, "")',
            'outputType' => 'string',
        ], $column->toArray()
        );
    }

    public function testDefaults()
    {
        $column = new VirtualColumn('if(mccmnc > 0, country_iso, "")', 'country_iso');

        $this->assertEquals([
            'type'       => 'expression',
            'name'       => 'country_iso',
            'expression' => 'if(mccmnc > 0, country_iso, "")',
            'outputType' => 'float',
        ], $column->toArray()
        );
    }

    public function testIncorrectType()
    {
        $this->expectException(InvalidArgumentException::class);

        new VirtualColumn('a', 'b', 'something');
    }
}
