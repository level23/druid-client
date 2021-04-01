<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\VirtualColumns;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\VirtualColumns\VirtualColumn;

class VirtualColumnTest extends TestCase
{
    public function testVirtualColumn(): void
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

    public function testDefaults(): void
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

    public function testIncorrectType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new VirtualColumn('a', 'b', 'something');
    }
}
