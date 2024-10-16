<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Lookups;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Lookups\KafkaLookup;

class KafkaLookupTest extends TestCase
{
    public function testLookup(): void
    {
        $lookup = new KafkaLookup(
            'operators',
            ['kafka1.server:9092', 'kafka2.server:9092'],
        );

        $this->assertEquals(
            [
                'type'            => 'kafka',
                'kafkaTopic'      => 'operators',
                'kafkaProperties' => [
                    'bootstrap.servers' => 'kafka1.server:9092,kafka2.server:9092',
                ],
                'connectTimeout'  => 0,
                'isOneToOne'      => false,
            ],
            $lookup->toArray()
        );

        $lookup = new KafkaLookup(
            'countries',
            'kafka3.server:9092,kafka4.server:9092',
            ['group.id' => 'myGroup'],
            6000,
            true
        );

        $this->assertEquals(
            [
                'type'            => 'kafka',
                'kafkaTopic'      => 'countries',
                'kafkaProperties' => [
                    'bootstrap.servers' => 'kafka3.server:9092,kafka4.server:9092',
                    'group.id'          => 'myGroup',
                ],
                'connectTimeout'  => 6000,
                'isOneToOne'      => true,
            ],
            $lookup->toArray()
        );
    }
}
