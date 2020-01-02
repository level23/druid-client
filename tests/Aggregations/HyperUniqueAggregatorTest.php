<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Aggregations;

use tests\TestCase;
use Level23\Druid\Aggregations\HyperUniqueAggregator;

class HyperUniqueAggregatorTest extends TestCase
{
    /**
     * @testWith [true, true]
     *           [false, true]
     *           [false, false]
     *
     * @param bool $isInputHyperUnique
     * @param bool $round
     */
    public function testAggregation(bool $isInputHyperUnique, bool $round)
    {
        $aggregator = new HyperUniqueAggregator(
            'hyperHyper',
            'scooter',
            $isInputHyperUnique,
            $round
        );
        $this->assertEquals([
            'type'               => 'hyperUnique',
            'name'               => 'hyperHyper',
            'fieldName'          => 'scooter',
            'isInputHyperUnique' => $isInputHyperUnique,
            'round'              => $round,
        ], $aggregator->toArray());
    }

    public function testDefaults()
    {
        $aggregator = new HyperUniqueAggregator(
            'hyperHyper',
            'scooter'
        );
        $this->assertEquals([
            'type'               => 'hyperUnique',
            'name'               => 'hyperHyper',
            'fieldName'          => 'scooter',
            'isInputHyperUnique' => false,
            'round'              => false,
        ], $aggregator->toArray());
    }
}
