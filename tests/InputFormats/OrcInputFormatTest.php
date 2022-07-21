<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\FlattenFieldType;
use Level23\Druid\InputFormats\FlattenSpec;
use Level23\Druid\InputFormats\OrcInputFormat;

class OrcInputFormatTest extends TestCase
{
    public function testInputFormat(): void
    {
        $input = new OrcInputFormat();

        $this->assertEquals([
            'type' => 'orc',
        ], $input->toArray());

        $flattenSpec = new FlattenSpec(true);
        $flattenSpec->field(FlattenFieldType::PATH, 'myField', 'input.a.b');

        $input = new OrcInputFormat($flattenSpec);

        $this->assertEquals([
            'type'        => 'orc',
            'flattenSpec' => $flattenSpec->toArray(),
        ], $input->toArray());

        $input = new OrcInputFormat($flattenSpec, true);

        $this->assertEquals([
            'type'           => 'orc',
            'flattenSpec'    => $flattenSpec->toArray(),
            'binaryAsString' => true,
        ], $input->toArray());

        $input = new OrcInputFormat(null, false);

        $this->assertEquals([
            'type'           => 'orc',
            'binaryAsString' => false,
        ], $input->toArray());
    }
}