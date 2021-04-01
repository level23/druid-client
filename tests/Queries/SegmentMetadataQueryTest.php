<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Queries;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Queries\SegmentMetadataQuery;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Responses\SegmentMetadataQueryResponse;

class SegmentMetadataQueryTest extends TestCase
{
    public function testQuery(): void
    {
        $dataSource = 'hardware';
        $intervals  = new IntervalCollection(new Interval('12-02-2019', '13-02-2019'));

        $query = new SegmentMetadataQuery($dataSource, $intervals);

        $this->assertEquals([
            'queryType'  => 'segmentMetadata',
            'dataSource' => $dataSource,
            'intervals'  => $intervals->toArray(),
        ], $query->toArray());

        $response = $query->parseResponse([]);

        $this->assertInstanceOf(SegmentMetadataQueryResponse::class, $response);
    }
}
