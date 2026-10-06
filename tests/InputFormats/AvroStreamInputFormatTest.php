<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\FlattenFieldType;
use Level23\Druid\InputFormats\FlattenSpec;
use Level23\Druid\InputFormats\AvroStreamInputFormat;

class AvroStreamInputFormatTest extends TestCase
{
    public function testInputFormat(): void
    {
        $decoder = [
            'type'           => 'schema_registry',
            'url'            => 'http://schema-registry:8081',
            'capacity'       => 100,
            'urls'           => ['http://schema-registry:8081'],
            'config'         => [],
            'headers'        => [],
        ];

        $input = new AvroStreamInputFormat($decoder);

        $this->assertEquals([
            'type'             => 'avro_stream',
            'avroBytesDecoder' => $decoder,
        ], $input->toArray());

        $flattenSpec = new FlattenSpec(true);
        $flattenSpec->field(FlattenFieldType::PATH, 'someField', 'input.a.b');

        $input = new AvroStreamInputFormat($decoder, $flattenSpec, true, true);

        $this->assertEquals([
            'type'                => 'avro_stream',
            'avroBytesDecoder'    => $decoder,
            'flattenSpec'         => $flattenSpec->toArray(),
            'binaryAsString'      => true,
            'extractUnionsByType' => true,
        ], $input->toArray());
    }
}
