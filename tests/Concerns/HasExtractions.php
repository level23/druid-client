<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Level23\Druid\Extractions\CascadeExtraction;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Extractions\ExtractionInterface;
use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Extractions\PartialExtraction;
use Level23\Druid\Extractions\RegexExtraction;
use Level23\Druid\Extractions\SearchQueryExtraction;
use Level23\Druid\Extractions\SubstringExtraction;
use Level23\Druid\Extractions\TimeFormatExtraction;
use Mockery;
use tests\TestCase;

class HasExtractions extends TestCase
{
    /**
     * @param string $class
     *
     * @return \Mockery\Generator\MockConfigurationBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getExtractionMock(string $class)
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        $builder->addTarget(ExtractionInterface::class);

        return Mockery::mock($builder);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLookup()
    {
        $this->getExtractionMock(LookupExtraction::class)
            ->shouldReceive('__construct')
            ->with('username', 'Unknown', false, true);

        $builder = new ExtractionBuilder();
        $builder->lookup('username', 'Unknown', false, true);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLookupDefaults()
    {
        $this->getExtractionMock(LookupExtraction::class)
            ->shouldReceive('__construct')
            ->with('username', true, true, null);

        $builder = new ExtractionBuilder();
        $builder->lookup('username');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPartial()
    {
        $this->getExtractionMock(PartialExtraction::class)
            ->shouldReceive('__construct')
            ->with('[a-z]{1,9}');

        $builder = new ExtractionBuilder();
        $builder->partial('[a-z]{1,9}');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegex()
    {
        $this->getExtractionMock(RegexExtraction::class)
            ->shouldReceive('__construct')
            ->with('[a-z]{1,9}', 2, false);

        $builder = new ExtractionBuilder();
        $builder->regex('[a-z]{1,9}', 2, false);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegexDefaults()
    {
        $this->getExtractionMock(RegexExtraction::class)
            ->shouldReceive('__construct')
            ->with('[a-z]{1,9}', 1, true);

        $builder = new ExtractionBuilder();
        $builder->regex('[a-z]{1,9}');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSearchQuery()
    {
        $this->getExtractionMock(SearchQueryExtraction::class)
            ->shouldReceive('__construct')
            ->with('john', true);

        $builder = new ExtractionBuilder();
        $builder->searchQuery('john', true);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSearchQueryDefaults()
    {
        $this->getExtractionMock(SearchQueryExtraction::class)
            ->shouldReceive('__construct')
            ->with(['john', 'ben'], false);

        $builder = new ExtractionBuilder();
        $builder->searchQuery(['john', 'ben'], false);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSubstring()
    {
        $this->getExtractionMock(SubstringExtraction::class)
            ->shouldReceive('__construct')
            ->with(2, 2);

        $builder = new ExtractionBuilder();
        $builder->substring(2, 2);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSubstringDefaults()
    {
        $this->getExtractionMock(SubstringExtraction::class)
            ->shouldReceive('__construct')
            ->with(2, null);

        $builder = new ExtractionBuilder();
        $builder->substring(2, null);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTimeFormat()
    {
        $this->getExtractionMock(TimeFormatExtraction::class)
            ->shouldReceive('__construct')
            ->with('yyyy-MM-dd HH:00:00', 'hour', 'fr', 'Europe/Amsterdam', false);

        $builder = new ExtractionBuilder();
        $builder->timeFormat('yyyy-MM-dd HH:00:00', 'hour', 'fr', 'Europe/Amsterdam', false);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTimeFormatWithDefaults()
    {
        $this->getExtractionMock(TimeFormatExtraction::class)
            ->shouldReceive('__construct')
            ->with(null, null, null, null, null);

        $builder = new ExtractionBuilder();
        $builder->timeFormat();
    }

    public function testMultiple()
    {
        $builder = new ExtractionBuilder();
        $builder->timeFormat();
        $builder->substring(1, 2);

        $extraction = $builder->getExtraction();
        $this->assertInstanceOf(CascadeExtraction::class, $extraction);

        if ($extraction instanceof CascadeExtraction) {
            $array = $extraction->toArray();
            $this->assertEquals(2, count($array['extractionFns']));
        }
    }
}