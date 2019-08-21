<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

use tests\TestCase;

class SearchQueryExtractionTest extends TestCase
{
    public function testExtraction()
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
