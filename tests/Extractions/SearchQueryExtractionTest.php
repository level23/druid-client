<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Extractions\SearchQueryExtraction;

class SearchQueryExtractionTest extends TestCase
{
    public function testExtraction(): void
    {
        $extraction = new SearchQueryExtraction('john', true);
        $this->assertEquals([
            'type'           => 'contains',
            'case_sensitive' => true,
            'value'          => 'john',
        ], $extraction->toArray());

        $extraction = new SearchQueryExtraction(['john', 'doe']);
        $this->assertEquals([
            'type'           => 'fragment',
            'case_sensitive' => false,
            'values'         => ['john', 'doe'],
        ], $extraction->toArray());
    }
}
