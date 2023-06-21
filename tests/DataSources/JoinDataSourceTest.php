<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\DataSources;

use ValueError;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\JoinType;
use Level23\Druid\DataSources\JoinDataSource;
use Level23\Druid\DataSources\TableDataSource;

class JoinDataSourceTest extends TestCase
{
    public function testJoinDataSource(): void
    {
        $left  = new TableDataSource('table1');
        $right = new TableDataSource('table2');

        $dataSource = new JoinDataSource(
            $left,
            $right,
            'p.',
            'p.country_id = country_id',
            JoinType::INNER
        );

        $this->assertEquals([
            'type'        => 'join',
            'left'        => $left->toArray(),
            'right'       => $right->toArray(),
            'rightPrefix' => 'p.',
            'condition'   => 'p.country_id = country_id',
            'joinType'    => JoinType::INNER->value,
        ], $dataSource->toArray());
    }

    /**
     * @testWith ["INnEr", false]
     *           ["LEFT", false]
     *           ["LefT", false]
     *           ["left", false]
     *           ["outer", true]
     *           [" left ", true]
     *           ["", true]
     *
     * @param string $value
     * @param bool   $expectException
     *
     * @return void
     */
    public function testJoinType(string $value, bool $expectException): void
    {
        if ($expectException) {
            $this->expectException(ValueError::class);
            $this->expectExceptionMessage(
                '"'.strtoupper($value).'" is not a valid backing value for enum Level23\Druid\Types\JoinType'
            );
        }

        $this->assertEquals(strtoupper($value), JoinType::from(strtoupper($value))->value);
    }
}