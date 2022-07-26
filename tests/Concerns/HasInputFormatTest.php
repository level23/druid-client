<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Tasks\IndexTaskBuilder;
use Level23\Druid\Types\FlattenFieldType;
use Level23\Druid\InputFormats\FlattenSpec;
use Level23\Druid\InputFormats\OrcInputFormat;
use Level23\Druid\InputFormats\CsvInputFormat;
use Level23\Druid\InputFormats\TsvInputFormat;
use Level23\Druid\InputFormats\JsonInputFormat;
use Level23\Druid\InputFormats\ParquetInputFormat;
use Level23\Druid\InputFormats\ProtobufInputFormat;
use Level23\Druid\InputFormats\InputFormatInterface;

class HasInputFormatTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testJsonFormat(): void
    {
        $client  = new DruidClient([]);
        $builder = new IndexTaskBuilder($client, 'animals');

        $flattenSpec = new FlattenSpec(true);
        $flattenSpec->field(FlattenFieldType::ROOT, 'blah');

        $jsonInputFormat = $this->getConstructorMock(JsonInputFormat::class, InputFormatInterface::class);
        $jsonInputFormat->shouldReceive('__construct')
            ->once()
            ->with($flattenSpec, ['ALLOW_COMMENTS' => true]);

        $this->assertEquals(
            $builder,
            $builder->jsonFormat($flattenSpec, ['ALLOW_COMMENTS' => true])
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testOrcFormat(): void
    {
        $client  = new DruidClient([]);
        $builder = new IndexTaskBuilder($client, 'animals');

        $flattenSpec = new FlattenSpec(true);
        $flattenSpec->field(FlattenFieldType::ROOT, 'blah');

        $jsonInputFormat = $this->getConstructorMock(OrcInputFormat::class, InputFormatInterface::class);
        $jsonInputFormat->shouldReceive('__construct')
            ->once()
            ->with($flattenSpec, true);

        $this->assertEquals(
            $builder,
            $builder->orcFormat($flattenSpec, true)
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testParquetFormat(): void
    {
        $client  = new DruidClient([]);
        $builder = new IndexTaskBuilder($client, 'animals');

        $flattenSpec = new FlattenSpec(true);
        $flattenSpec->field(FlattenFieldType::ROOT, 'blah');

        $jsonInputFormat = $this->getConstructorMock(ParquetInputFormat::class, InputFormatInterface::class);
        $jsonInputFormat->shouldReceive('__construct')
            ->once()
            ->with($flattenSpec, false);

        $this->assertEquals(
            $builder,
            $builder->parquetFormat($flattenSpec, false)
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testProtobufFormat(): void
    {
        $client  = new DruidClient([]);
        $builder = new IndexTaskBuilder($client, 'animals');

        $decoder = [
            "type"             => "file",
            "descriptor"       => "file:///tmp/metrics.desc",
            "protoMessageType" => "Metrics",
        ];

        $flattenSpec = new FlattenSpec(true);
        $flattenSpec->field(FlattenFieldType::ROOT, 'blah');

        $jsonInputFormat = $this->getConstructorMock(ProtobufInputFormat::class, InputFormatInterface::class);
        $jsonInputFormat->shouldReceive('__construct')
            ->once()
            ->with($decoder, $flattenSpec);

        $this->assertEquals(
            $builder,
            $builder->protobufFormat($decoder, $flattenSpec)
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCsvFormat(): void
    {
        $client  = new DruidClient([]);
        $builder = new IndexTaskBuilder($client, 'animals');

        $jsonInputFormat = $this->getConstructorMock(CsvInputFormat::class, InputFormatInterface::class);
        $jsonInputFormat->shouldReceive('__construct')
            ->once()
            ->with(['name', 'age'], '|', true, 2);

        $this->assertEquals(
            $builder,
            $builder->csvFormat(['name', 'age'], '|', true, 2)
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTsvFormat(): void
    {
        $client  = new DruidClient([]);
        $builder = new IndexTaskBuilder($client, 'animals');

        $jsonInputFormat = $this->getConstructorMock(TsvInputFormat::class, InputFormatInterface::class);
        $jsonInputFormat->shouldReceive('__construct')
            ->once()
            ->with(['name', 'age'], ',', '|', true, 2);

        $this->assertEquals(
            $builder,
            $builder->tsvFormat(['name', 'age'], ',', '|', true, 2)
        );
    }
}