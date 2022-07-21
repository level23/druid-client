<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\FlattenFieldType;
use Level23\Druid\InputFormats\FlattenSpec;
use Level23\Druid\InputFormats\ParquetInputFormat;

class ParquetInputFormatTest extends TestCase
{
    public function testInputFormat(): void
    {
        $input = new ParquetInputFormat();

        $this->assertEquals([
            'type' => 'parquet',
        ], $input->toArray());

        $flattenSpec = new FlattenSpec(true);
        $flattenSpec->field(FlattenFieldType::PATH, 'myField', 'input.a.b');

        $input = new ParquetInputFormat($flattenSpec);

        $this->assertEquals([
            'type'        => 'parquet',
            'flattenSpec' => $flattenSpec->toArray(),
        ], $input->toArray());

        $input = new ParquetInputFormat($flattenSpec, true);

        $this->assertEquals([
            'type'           => 'parquet',
            'flattenSpec'    => $flattenSpec->toArray(),
            'binaryAsString' => true,
        ], $input->toArray());

        $input = new ParquetInputFormat(null, false);

        $this->assertEquals([
            'type'           => 'parquet',
            'binaryAsString' => false,
        ], $input->toArray());
    }
}