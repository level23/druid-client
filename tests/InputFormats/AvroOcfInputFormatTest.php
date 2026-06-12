<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\FlattenFieldType;
use Level23\Druid\InputFormats\FlattenSpec;
use Level23\Druid\InputFormats\AvroOcfInputFormat;

class AvroOcfInputFormatTest extends TestCase
{
    public function testInputFormat(): void
    {
        $input = new AvroOcfInputFormat();

        $this->assertEquals(['type' => 'avro_ocf'], $input->toArray());

        $flattenSpec = new FlattenSpec(true);
        $flattenSpec->field(FlattenFieldType::PATH, 'someField', 'input.a.b');

        $schema = [
            'namespace' => 'org.apache.druid',
            'name'      => 'wikipedia',
            'type'      => 'record',
            'fields'    => [
                ['name' => 'timestamp', 'type' => 'string'],
                ['name' => 'page', 'type' => 'string'],
            ],
        ];

        $input = new AvroOcfInputFormat($flattenSpec, $schema, true, true);

        $this->assertEquals([
            'type'                => 'avro_ocf',
            'flattenSpec'         => $flattenSpec->toArray(),
            'schema'              => $schema,
            'binaryAsString'      => true,
            'extractUnionsByType' => true,
        ], $input->toArray());
    }
}
