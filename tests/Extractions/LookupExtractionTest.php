<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use Level23\Druid\Extractions\LookupExtraction;
use tests\TestCase;

class LookupExtractionTest extends TestCase
{
    public function dataProvider(): array
    {
        $arguments = [];
        foreach (['numbers'] as $lookup) {
            foreach ([true, false, 'UNKNOWN'] as $keepMissing) {
                foreach ([true, false] as $optimize) {
                    foreach ([null, true, false] as $injective) {
                        $arguments[] = [
                            $lookup,
                            $keepMissing,
                            $optimize,
                            $injective,
                        ];
                    }
                }
            }
        }

        return $arguments;
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string      $lookup
     * @param string|bool $keepMissing
     * @param bool        $optimize
     * @param bool|null   $injective
     */
    public function testExtractionFunction(
        string $lookup,
        $keepMissing,
        bool $optimize,
        ?bool $injective
    ) {
        $extraction = new LookupExtraction(
            $lookup,
            $keepMissing,
            $optimize,
            $injective
        );
        $expected   = [
            'type'     => 'registeredLookup',
            'lookup'   => $lookup,
            'optimize' => $optimize,
        ];

        if ($injective !== null) {
            $expected['injective'] = $injective;
        }

        if (is_string($keepMissing)) {
            $expected['replaceMissingValueWith'] = $keepMissing;
        } elseif ($keepMissing) {
            $expected['retainMissingValue'] = $keepMissing;
        }

        $this->assertEquals($expected, $extraction->toArray());
    }

    public function testExtractionFunctionDefaults()
    {
        $extraction = new LookupExtraction('user');
        $expected   = [
            'type'               => 'registeredLookup',
            'lookup'             => "user",
            'optimize'           => true,
            'retainMissingValue' => true,
        ];

        $this->assertEquals($expected, $extraction->toArray());
    }
}