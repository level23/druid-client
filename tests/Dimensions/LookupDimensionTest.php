<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Dimensions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\LookupDimension;

class LookupDimensionTest extends TestCase
{
    public function testDimension(): void
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

    /**
     * @testWith [true, true]
     *           [false, false]
     *           [true, "-"]
     *
     * @param bool        $isOneToOne
     * @param bool|string $keepMissingValue
     *
     * @return void
     */
    public function testDimensionWithArray(bool $isOneToOne, bool|string $keepMissingValue): void
    {
        $lookupDimension = new LookupDimension(
            'number_id',
            [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'],
            'number',
            $keepMissingValue,
            $isOneToOne
        );

        $this->assertEquals('number', $lookupDimension->getOutputName());
        $this->assertEquals('number_id', $lookupDimension->getDimension());

        $expected = [
            'type'       => 'lookup',
            'dimension'  => 'number_id',
            'outputName' => 'number',
            'lookup'     => [
                'type'       => 'map',
                'map'        => [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'],
                'isOneToOne' => $isOneToOne,
            ],
        ];

        if ($keepMissingValue === true) {
            $expected['retainMissingValue'] = true;
        } elseif (is_string($keepMissingValue)) {
            $expected['replaceMissingValueWith'] = $keepMissingValue;
        }
        $this->assertEquals($expected, $lookupDimension->toArray());
    }

    public function testDimensionWithRetainMissingValue(): void
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

    public function testDimensionReplaceMissingWith(): void
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
