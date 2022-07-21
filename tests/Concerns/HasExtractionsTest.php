<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Mockery;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\NullHandling;
use Level23\Druid\Extractions\RegexExtraction;
use Level23\Druid\Extractions\UpperExtraction;
use Level23\Druid\Extractions\LowerExtraction;
use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Extractions\BucketExtraction;
use Level23\Druid\Extractions\CascadeExtraction;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Extractions\PartialExtraction;
use Level23\Druid\Extractions\ExtractionInterface;
use Level23\Druid\Extractions\SubstringExtraction;
use Level23\Druid\Extractions\TimeParseExtraction;
use Level23\Druid\Extractions\TimeFormatExtraction;
use Level23\Druid\Extractions\JavascriptExtraction;
use Level23\Druid\Extractions\SearchQueryExtraction;
use Level23\Druid\Extractions\InlineLookupExtraction;
use Level23\Druid\Extractions\StringFormatExtraction;

class HasExtractionsTest extends TestCase
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
    public function testLookup(): void
    {
        $this->getExtractionMock(LookupExtraction::class)
            ->shouldReceive('__construct')
            ->with('username', 'Unknown', false, true);

        $builder  = new ExtractionBuilder();
        $response = $builder->lookup('username', 'Unknown', false, true);

        $this->assertEquals($builder, $response);

        $this->assertInstanceOf(LookupExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLookupDefaults(): void
    {
        $this->getExtractionMock(LookupExtraction::class)
            ->shouldReceive('__construct')
            ->with('username', false, true, null);

        $builder  = new ExtractionBuilder();
        $response = $builder->lookup('username');
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(LookupExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBucket(): void
    {
        $this->getExtractionMock(BucketExtraction::class)
            ->shouldReceive('__construct')
            ->with(5, 2);

        $builder  = new ExtractionBuilder();
        $response = $builder->bucket(5, 2);

        $this->assertEquals($builder, $response);

        $this->assertInstanceOf(BucketExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBucketDefault(): void
    {
        $this->getExtractionMock(BucketExtraction::class)
            ->shouldReceive('__construct')
            ->with(1, 0);

        $builder  = new ExtractionBuilder();
        $response = $builder->bucket();

        $this->assertEquals($builder, $response);

        $this->assertInstanceOf(BucketExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPartial(): void
    {
        $this->getExtractionMock(PartialExtraction::class)
            ->shouldReceive('__construct')
            ->with('[a-z]{1,9}');

        $builder  = new ExtractionBuilder();
        $response = $builder->partial('[a-z]{1,9}');
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(PartialExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegex(): void
    {
        $this->getExtractionMock(RegexExtraction::class)
            ->shouldReceive('__construct')
            ->with('[a-z]{1,9}', 2, false);

        $builder  = new ExtractionBuilder();
        $response = $builder->regex('[a-z]{1,9}', 2, false);
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(RegexExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegexDefaults(): void
    {
        $this->getExtractionMock(RegexExtraction::class)
            ->shouldReceive('__construct')
            ->with('[a-z]{1,9}', 1, true);

        $builder  = new ExtractionBuilder();
        $response = $builder->regex('[a-z]{1,9}');
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(RegexExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSearchQuery(): void
    {
        $this->getExtractionMock(SearchQueryExtraction::class)
            ->shouldReceive('__construct')
            ->with('john', true);

        $builder  = new ExtractionBuilder();
        $response = $builder->searchQuery('john', true);
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(SearchQueryExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSearchQueryDefaults(): void
    {
        $this->getExtractionMock(SearchQueryExtraction::class)
            ->shouldReceive('__construct')
            ->with(['john', 'ben'], false);

        $builder  = new ExtractionBuilder();
        $response = $builder->searchQuery(['john', 'ben']);
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(SearchQueryExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSubstring(): void
    {
        $this->getExtractionMock(SubstringExtraction::class)
            ->shouldReceive('__construct')
            ->with(2, 2);

        $builder  = new ExtractionBuilder();
        $response = $builder->substring(2, 2);
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(SubstringExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSubstringDefaults(): void
    {
        $this->getExtractionMock(SubstringExtraction::class)
            ->shouldReceive('__construct')
            ->with(2, null);

        $builder  = new ExtractionBuilder();
        $response = $builder->substring(2);
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(SubstringExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTimeFormat(): void
    {
        $this->getExtractionMock(TimeFormatExtraction::class)
            ->shouldReceive('__construct')
            ->with('yyyy-MM-dd HH:00:00', 'hour', 'fr', 'Europe/Amsterdam', false);

        $builder  = new ExtractionBuilder();
        $response = $builder->timeFormat('yyyy-MM-dd HH:00:00', 'hour', 'fr', 'Europe/Amsterdam', false);
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(TimeFormatExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTimeFormatWithDefaults(): void
    {
        $this->getExtractionMock(TimeFormatExtraction::class)
            ->shouldReceive('__construct')
            ->with(null, null, null, null, null);

        $builder  = new ExtractionBuilder();
        $response = $builder->timeFormat();
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(TimeFormatExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMultiple(): void
    {
        $builder = new ExtractionBuilder();
        $builder->timeFormat();
        $builder->substring(1, 2);

        $extraction = $builder->getExtraction();
        $this->assertInstanceOf(CascadeExtraction::class, $extraction);

        if ($extraction instanceof CascadeExtraction) {
            /** @var array<string,array<mixed>> $array */
            $array = $extraction->toArray();
            $this->assertCount(2, $array['extractionFns']);
        }

        $builder->timeFormat();

        $extraction = $builder->getExtraction();
        $this->assertInstanceOf(CascadeExtraction::class, $extraction);

        if ($extraction instanceof CascadeExtraction) {
            /** @var array<string,array<mixed>> $array */
            $array = $extraction->toArray();
            $this->assertCount(3, $array['extractionFns']);
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInlineLookup(): void
    {
        $this->getExtractionMock(InlineLookupExtraction::class)
            ->shouldReceive('__construct')
            ->with(['f' => 'Female', 'm' => 'Male'],
                'Unknown',
                false,
                true
            );

        $builder  = new ExtractionBuilder();
        $response = $builder->inlineLookup(
            ['f' => 'Female', 'm' => 'Male'],
            'Unknown',
            false,
            true
        );

        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(InlineLookupExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInlineLookupWithDefaults(): void
    {
        $this->getExtractionMock(InlineLookupExtraction::class)
            ->shouldReceive('__construct')
            ->with(['f' => 'Female', 'm' => 'Male'], false, true, null);

        $builder  = new ExtractionBuilder();
        $response = $builder->inlineLookup(['f' => 'Female', 'm' => 'Male']);
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(InlineLookupExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFormat(): void
    {
        $this->getExtractionMock(StringFormatExtraction::class)
            ->shouldReceive('__construct')
            ->with('[%s]', NullHandling::EMPTY_STRING);

        $builder  = new ExtractionBuilder();
        $response = $builder->format('[%s]', NullHandling::EMPTY_STRING);

        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(StringFormatExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFormatDefaults(): void
    {
        $this->getExtractionMock(StringFormatExtraction::class)
            ->shouldReceive('__construct')
            ->with('[%s]', NullHandling::NULL_STRING);

        $builder  = new ExtractionBuilder();
        $response = $builder->format('[%s]');

        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(StringFormatExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testUpper(): void
    {
        $this->getExtractionMock(UpperExtraction::class)
            ->shouldReceive('__construct')
            ->with('fr');

        $builder  = new ExtractionBuilder();
        $response = $builder->upper('fr');
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(UpperExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLower(): void
    {
        $this->getExtractionMock(LowerExtraction::class)
            ->shouldReceive('__construct')
            ->with('fr');

        $builder  = new ExtractionBuilder();
        $response = $builder->lower('fr');
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(LowerExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTimeParse(): void
    {
        $this->getExtractionMock(TimeParseExtraction::class)
            ->shouldReceive('__construct')
            ->with('dd, MMMM, yyyy', 'yyyy-MM-dd HH:00:00', false);

        $builder  = new ExtractionBuilder();
        $response = $builder->timeParse('dd, MMMM, yyyy', 'yyyy-MM-dd HH:00:00', false);
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(TimeParseExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTimeParseWithDefaults(): void
    {
        $this->getExtractionMock(TimeParseExtraction::class)
            ->shouldReceive('__construct')
            ->with('dd, MMMM, yyyy', 'yyyy-MM-dd HH:00:00', true);

        $builder  = new ExtractionBuilder();
        $response = $builder->timeParse('dd, MMMM, yyyy', 'yyyy-MM-dd HH:00:00');
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(TimeParseExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testJavascript(): void
    {
        $this->getExtractionMock(JavascriptExtraction::class)
            ->shouldReceive('__construct')
            ->with('function() { return "hi"; }', true);

        $builder  = new ExtractionBuilder();
        $response = $builder->javascript('function() { return "hi"; }', true);
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(JavascriptExtraction::class, $builder->getExtraction());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testJavascriptWithDefaults(): void
    {
        $this->getExtractionMock(JavascriptExtraction::class)
            ->shouldReceive('__construct')
            ->with('function() { return "hi"; }', false);

        $builder  = new ExtractionBuilder();
        $response = $builder->javascript('function() { return "hi"; }');
        $this->assertEquals($builder, $response);
        $this->assertInstanceOf(JavascriptExtraction::class, $builder->getExtraction());
    }
}
