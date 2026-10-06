<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputFormats\CsvInputFormat;
use Level23\Druid\InputFormats\JsonInputFormat;
use Level23\Druid\InputFormats\KafkaInputFormat;

class KafkaInputFormatTest extends TestCase
{
    public function testInputFormatMinimal(): void
    {
        $value = new JsonInputFormat();

        $input = new KafkaInputFormat($value);

        $this->assertEquals([
            'type'        => 'kafka',
            'valueFormat' => $value->toArray(),
        ], $input->toArray());
    }

    public function testInputFormatFull(): void
    {
        $value  = new JsonInputFormat();
        $key    = new CsvInputFormat(['key']);
        $header = ['type' => 'string', 'encoding' => 'UTF-8'];

        $input = new KafkaInputFormat(
            $value,
            $key,
            $header,
            'kafka.h.',
            'kafka.k',
            'kafka.ts',
            'kafka.t'
        );

        $this->assertEquals([
            'type'                => 'kafka',
            'valueFormat'         => $value->toArray(),
            'keyFormat'           => $key->toArray(),
            'headerFormat'        => $header,
            'headerColumnPrefix'  => 'kafka.h.',
            'keyColumnName'       => 'kafka.k',
            'timestampColumnName' => 'kafka.ts',
            'topicColumnName'     => 'kafka.t',
        ], $input->toArray());
    }
}
