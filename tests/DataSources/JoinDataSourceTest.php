<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\DataSources;

use InvalidArgumentException;
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
            'joinType'    => JoinType::INNER,
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
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage(
                'The given join type is invalid: ' . strtoupper($value) . '. ' .
                'Allowed are: ' . implode(', ', JoinType::values())
            );
        }

        $this->assertEquals(strtoupper($value), JoinType::validate($value));
    }
}