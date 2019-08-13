<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Dimensions;

use Level23\Druid\Dimensions\LookupDimension;
use tests\TestCase;

class LookupDimensionTest extends TestCase
{
    public function testDimension()
    {
        $lookupDimension = new LookupDimension(
            'number_id',
            'numbers',
            'number'
        );

        $this->assertEquals([
            'type'       => 'lookup',
            'dimension'  => 'number_id',
            'outputName' => 'number',
            'name'       => 'numbers',
        ], $lookupDimension->getDimension());
    }

    public function testDimensionWithRetainMissingValue()
    {
        $lookupDimension = new LookupDimension(
            'number_id',
            'numbers',
            'number',
            true
        );

        $this->assertEquals([
            'type'               => 'lookup',
            'dimension'          => 'number_id',
            'outputName'         => 'number',
            'name'               => 'numbers',
            'retainMissingValue' => true,
        ], $lookupDimension->getDimension());
    }

    public function testDimensionReplaceMissingWith()
    {
        $lookupDimension = new LookupDimension(
            'number_id',
            'numbers',
            'number',
            true,
            'pieter'
        );

        $this->assertEquals([
            'type'                    => 'lookup',
            'dimension'               => 'number_id',
            'outputName'              => 'number',
            'name'                    => 'numbers',
            'replaceMissingValueWith' => 'pieter',
        ], $lookupDimension->getDimension());
    }
}