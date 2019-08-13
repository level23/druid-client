<?php
declare(strict_types=1);

namespace tests\Level23\Druid\ExtractionFunctions;

use Level23\Druid\Extractions\LookupExtraction;
use tests\TestCase;

class LookupExtractionFunctionTest extends TestCase
{
    public function dataProvider(): array
    {
        $arguments = [];
        foreach (['numbers'] as $lookup) {
            foreach ([true, false] as $retainMissingValue) {
                foreach ([null, 'UNKNOWN'] as $replaceMissingValueWith) {
                    foreach ([true, false] as $optimize) {
                        foreach ([null, true, false] as $injective) {
                            $arguments[] = [
                                $lookup,
                                $retainMissingValue,
                                $replaceMissingValueWith,
                                $optimize,
                                $injective,
                            ];
                        }
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
     * @param bool        $retainMissingValue
     * @param string|null $replaceMissingValueWith
     * @param bool        $optimize
     * @param bool|null   $injective
     */
    public function testExtractionFunction(
        string $lookup,
        bool $retainMissingValue,
        ?string $replaceMissingValueWith,
        bool $optimize,
        ?bool $injective
    ) {
        $extr     = new LookupExtraction(
            $lookup,
            $retainMissingValue,
            $replaceMissingValueWith,
            $optimize,
            $injective
        );
        $expected = [
            'type'     => 'registeredLookup',
            'lookup'   => $lookup,
            'optimize' => $optimize,
        ];

        if ($injective !== null) {
            $expected['injective'] = $injective;
        }

        if (!empty($replaceMissingValueWith)) {
            $expected['replaceMissingValueWith'] = $replaceMissingValueWith;
        } elseif ($retainMissingValue) {
            $expected['retainMissingValue'] = $retainMissingValue;
        }

        $this->assertEquals($expected, $extr->getExtractionFunction());
    }

    public function testExtractionFunctionDefaults()
    {
        $extr     = new LookupExtraction('user');
        $expected = [
            'type'               => 'registeredLookup',
            'lookup'             => "user",
            'optimize'           => true,
            'retainMissingValue' => true,
        ];

        $this->assertEquals($expected, $extr->getExtractionFunction());
    }
}