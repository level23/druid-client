<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\FlattenFieldType;
use Level23\Druid\InputFormats\FlattenSpec;
use Level23\Druid\InputFormats\ProtobufInputFormat;

class ProtobufInputFormatTest extends TestCase
{
    public function testInputFormat(): void
    {
        $decoder = [
            "type"             => "file",
            "descriptor"       => "file:///tmp/metrics.desc",
            "protoMessageType" => "Metrics",
        ];

        $input = new ProtobufInputFormat($decoder);

        $this->assertEquals([
            'type'              => 'protobuf',
            'protoBytesDecoder' => $decoder,
        ], $input->toArray());

        $flattenSpec = new FlattenSpec(true);
        $flattenSpec->field(FlattenFieldType::PATH, 'myField', 'input.a.b');

        $input = new ProtobufInputFormat($decoder, $flattenSpec);

        $this->assertEquals([
            'type'              => 'protobuf',
            'protoBytesDecoder' => $decoder,
            'flattenSpec'       => $flattenSpec->toArray(),
        ], $input->toArray());
    }
}