<?php

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\FilterBuilder;

class CombinationsTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testAndAndOrFilterCombination()
    {
        $client = new DruidClient([]);
        $query  = $client->query('source');
        $query->interval('2020-01-01', '2020-01-02');

        $query->where(function (FilterBuilder $builder) {
            $builder->orWhere('foo', '=', 'bar');
            $builder->orWhere('foo', '=', 'baz');
        });

        $query->where('bar', '=', 'qux');

        $result = $query->toArray();

        $expectedFilter = [
            'type'   => 'and',
            'fields' => [
                [
                    'type'   => 'or',
                    'fields' => [
                        [
                            'type'      => 'selector',
                            'dimension' => 'foo',
                            'value'     => 'bar',
                        ],
                        [
                            'type'      => 'selector',
                            'dimension' => 'foo',
                            'value'     => 'baz',
                        ],
                    ],
                ],
                [
                    'type'      => 'selector',
                    'dimension' => 'bar',
                    'value'     => 'qux',
                ],
            ],
        ];

        $this->assertEquals($expectedFilter, $result['filter']);
    }
}
