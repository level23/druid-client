<?php
declare(strict_types=1);

namespace tests\Level23\Druid\VirtualColumns;

use InvalidArgumentException;
use Level23\Druid\VirtualColumns\VirtualColumn;
use tests\TestCase;

class VirtualColumnTest extends TestCase
{
    public function testVirtualColumn()
    {
        $column = new VirtualColumn('country_iso', 'if(mccmnc > 0, country_iso, "")', 'string');

        $this->assertEquals([
            'type'       => 'expression',
            'name'       => 'country_iso',
            'expression' => 'if(mccmnc > 0, country_iso, "")',
            'outputType' => 'string',
        ], $column->getVirtualColumn()
        );
    }

    public function testDefaults()
    {
        $column = new VirtualColumn('country_iso', 'if(mccmnc > 0, country_iso, "")');

        $this->assertEquals([
            'type'       => 'expression',
            'name'       => 'country_iso',
            'expression' => 'if(mccmnc > 0, country_iso, "")',
            'outputType' => 'float',
        ], $column->getVirtualColumn()
        );
    }

    public function testIncorrectType()
    {
        $this->expectException(InvalidArgumentException::class);

        new VirtualColumn('a', 'b', 'blaat');
    }
}