<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use tests\Level23\Druid\TestCase;
use Level23\Druid\Extractions\InlineLookupExtraction;

class InlineLookupExtractionTest extends TestCase
{
    /**
     * @testWith [true, true, true]
     *           [true, false, true]
     *           [true, false, false]
     *           [true, true, false]
     *           [false, true, true]
     *           [false, true, false]
     *           [false, false, true ]
     *           [false, false, false ]
     *           ["Unknown", true, true]
     *           ["Unknown", true, false]
     *           ["Unknown", false, true]
     *           ["Unknown", false, false]
     *
     * @param bool|string $replaceMissingWith
     * @param bool        $optimize
     * @param bool        $injective
     */
    public function testExtractionFunction($replaceMissingWith, bool $optimize, bool $injective)
    {
        $extraction = new InlineLookupExtraction(
            ['m' => 'Male', 'f' => 'Female'],
            $replaceMissingWith,
            $optimize,
            $injective
        );

        $retainMissingValue      = is_string($replaceMissingWith) ? true : (bool)$replaceMissingWith;
        $replaceMissingValueWith = is_string($replaceMissingWith) ? $replaceMissingWith : null;

        $expected = [
            'type'     => 'lookup',
            'lookup'   => [
                'type' => 'map',
                'map'  => ['m' => 'Male', 'f' => 'Female'],
            ],
            'optimize' => $optimize,
        ];

        if ($injective !== null) {
            $expected['injective'] = $injective;
        }

        if ($replaceMissingValueWith !== null) {
            $expected['replaceMissingValueWith'] = $replaceMissingValueWith;
        } elseif ($retainMissingValue) {
            $expected['retainMissingValue'] = $retainMissingValue;
        }

        $this->assertEquals($expected, $extraction->toArray());
    }

    public function testExtractionFunctionDefaults()
    {
        $extraction = new InlineLookupExtraction(['y' => 'Yes', 'n' => 'No']);
        $expected   = [
            'type'     => 'lookup',
            'lookup'   => [
                'type' => 'map',
                'map'  => ['y' => 'Yes', 'n' => 'No'],
            ],
            'optimize' => true,
        ];

        $this->assertEquals($expected, $extraction->toArray());
    }
}
