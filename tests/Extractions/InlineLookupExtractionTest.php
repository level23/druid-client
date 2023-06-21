<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use Level23\Druid\Tests\TestCase;
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
    public function testExtractionFunction(bool|string $replaceMissingWith, bool $optimize, bool $injective): void
    {
        $extraction = new InlineLookupExtraction(
            ['m' => 'Male', 'f' => 'Female'],
            $replaceMissingWith,
            $optimize,
            $injective
        );

        $retainMissingValue      = is_string($replaceMissingWith) || $replaceMissingWith;
        $replaceMissingValueWith = is_string($replaceMissingWith) ? $replaceMissingWith : null;

        $expected = [
            'type'     => 'lookup',
            'lookup'   => [
                'type' => 'map',
                'map'  => ['m' => 'Male', 'f' => 'Female'],
            ],
            'optimize' => $optimize,
        ];

        $expected['injective'] = $injective;

        if ($replaceMissingValueWith !== null) {
            $expected['replaceMissingValueWith'] = $replaceMissingValueWith;
        } elseif ($retainMissingValue) {
            $expected['retainMissingValue'] = true;
        }

        $this->assertEquals($expected, $extraction->toArray());
    }

    public function testExtractionFunctionDefaults(): void
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
