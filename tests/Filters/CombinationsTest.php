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
    public function testAndAndOrFilterCombination(): void
    {
        $client = new DruidClient([]);
        $query  = $client->query('source');
        $query->interval('2020-01-01', '2020-01-02');

        $query->where(function (FilterBuilder $builder) {
            $builder->orWhere('foo', '=', 5.3);
            $builder->orWhere('foo', '=', 1);
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
                            'type'           => 'equals',
                            'column'         => 'foo',
                            'matchValueType' => 'double',
                            'matchValue'     => 5.3,
                        ],
                        [
                            'type'           => 'equals',
                            'column'         => 'foo',
                            'matchValueType' => 'long',
                            'matchValue'     => 1,
                        ],
                    ],
                ],
                [
                    'type'           => 'equals',
                    'column'         => 'bar',
                    'matchValueType' => 'string',
                    'matchValue'     => 'qux',
                ],
            ],
        ];

        $this->assertEquals($expectedFilter, $result['filter']);
    }
}
