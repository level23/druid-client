<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Extractions\BucketExtraction;

class BucketExtractionTest extends TestCase
{
    public function testBucket()
    {
        $extraction = new BucketExtraction(5, 2);

        $this->assertEquals([
            'type'   => 'bucket',
            'size'   => 5,
            'offset' => 2,
        ], $extraction->toArray());
    }

    public function testBucketDefaults()
    {
        $extraction = new BucketExtraction();

        $this->assertEquals([
            'type'   => 'bucket',
            'size'   => 1,
            'offset' => 0,
        ], $extraction->toArray());
    }
}
