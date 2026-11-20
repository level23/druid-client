<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;
use Level23\Druid\PostAggregations\ThetaSketchEstimatePostAggregator;

class ThetaSketchEstimatePostAggregatorTest extends TestCase
{
    public function testAggregator(): void
    {
        $fieldAccess = new FieldAccessPostAggregator('users', 'users');
        $aggregator  = new ThetaSketchEstimatePostAggregator($fieldAccess, 'unique_users');

        $this->assertEquals([
            'type'  => 'thetaSketchEstimate',
            'name'  => 'unique_users',
            'field' => $fieldAccess->toArray(),
        ], $aggregator->toArray());
    }

    public function testAggregatorWithErrorBounds(): void
    {
        $fieldAccess = new FieldAccessPostAggregator('users', 'users');
        $aggregator  = new ThetaSketchEstimatePostAggregator($fieldAccess, 'unique_users', 2);

        $this->assertEquals([
            'type'              => 'thetaSketchEstimate',
            'name'              => 'unique_users',
            'field'             => $fieldAccess->toArray(),
            'errorBoundsStdDev' => 2,
        ], $aggregator->toArray());
    }
}
