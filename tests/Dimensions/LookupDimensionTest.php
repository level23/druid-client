<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Dimensions;

use tests\TestCase;
use Level23\Druid\Dimensions\LookupDimension;

class LookupDimensionTest extends TestCase
{
    public function testDimension()
    {
        $lookupDimension = new LookupDimension(
            'number_id',
            'numbers',
            'number'
        );

        $this->assertEquals('number', $lookupDimension->getOutputName());
        $this->assertEquals('number_id', $lookupDimension->getDimension());

        $this->assertEquals([
            'type'       => 'lookup',
            'dimension'  => 'number_id',
            'outputName' => 'number',
            'name'       => 'numbers',
        ], $lookupDimension->toArray());
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
        ], $lookupDimension->toArray());
    }

    public function testDimensionReplaceMissingWith()
    {
        $lookupDimension = new LookupDimension(
            'number_id',
            'numbers',
            'number',
            'pieter'
        );

        $this->assertEquals([
            'type'                    => 'lookup',
            'dimension'               => 'number_id',
            'outputName'              => 'number',
            'name'                    => 'numbers',
            'replaceMissingValueWith' => 'pieter',
        ], $lookupDimension->toArray());
    }
}