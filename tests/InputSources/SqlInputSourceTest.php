<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputSources\SqlInputSource;

class SqlInputSourceTest extends TestCase
{
    public function testSqlInputSource(): void
    {
        $sqls = [
            "select * from users where name = 'foo'",
            "select * from users where name = 'bar'",
        ];

        $sql = new SqlInputSource(
            "jdbc:mysql://host:port/schema",
            "username",
            "password",
            $sqls
        );

        $this->assertEquals([
            'type'     => 'sql',
            'database' => [
                'type'            => 'mysql',
                "connectorConfig" => [
                    'connectURI' => 'jdbc:mysql://host:port/schema',
                    'user'       => 'username',
                    'password'   => 'password',
                ],
            ],
            'sqls'     => $sqls,
        ], $sql->toArray());
    }

    public function testSqlInputSourcePostgres(): void
    {
        $sqls = [
            "select * from users where name = 'foo'",
            "select * from users where name = 'bar'",
        ];

        $sql = new SqlInputSource(
            'jdbc:postgresql://host:port/schema',
            'MyUsername',
            'MyPassword',
            $sqls,
            true
        );

        $this->assertEquals([
            'type'     => 'sql',
            'database' => [
                'type'            => 'postgresql',
                "connectorConfig" => [
                    'connectURI' => 'jdbc:postgresql://host:port/schema',
                    'user'       => 'MyUsername',
                    'password'   => 'MyPassword',
                ],
            ],
            'foldCase' => true,
            'sqls'     => $sqls,
        ], $sql->toArray());
    }
}