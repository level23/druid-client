<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\FlattenFieldType;
use Level23\Druid\InputFormats\FlattenSpec;
use Level23\Druid\InputFormats\JsonInputFormat;

class JsonInputFormatTest extends TestCase
{
    public function testInputFormat(): void
    {
        $input = new JsonInputFormat();

        $this->assertEquals([
            'type' => 'json',
        ], $input->toArray());

        $flattenSpec = new FlattenSpec(true);
        $flattenSpec->field(FlattenFieldType::PATH, 'myField', 'input.a.b');

        $input = new JsonInputFormat($flattenSpec);

        $this->assertEquals([
            'type'        => 'json',
            'flattenSpec' => $flattenSpec->toArray(),
        ], $input->toArray());

        $input = new JsonInputFormat($flattenSpec, ['ALLOW_COMMENTS' => true]);

        $this->assertEquals([
            'type'        => 'json',
            'flattenSpec' => $flattenSpec->toArray(),
            'featureSpec' => ['ALLOW_COMMENTS' => true],
        ], $input->toArray());

        $input = new JsonInputFormat(null, ['ALLOW_COMMENTS' => true]);

        $this->assertEquals([
            'type'                => 'json',
            'featureSpec' => ['ALLOW_COMMENTS' => true],
        ], $input->toArray());
    }
}