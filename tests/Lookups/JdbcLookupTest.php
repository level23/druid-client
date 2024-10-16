<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Lookups;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Lookups\JdbcLookup;

class JdbcLookupTest extends TestCase
{
    public function testLookup(): void
    {
        $lookup = new JdbcLookup(
            'jdbc:mysql://localhost:3306/druid',
            null,
            null,
            'countries',
            'country_iso',
            'country_name',
        );
        $this->assertEquals(
            [
                'type'                => 'cachedNamespace',
                'extractionNamespace' => [
                    'type'            => 'jdbc',
                    'connectorConfig' => [
                        'connectURI' => 'jdbc:mysql://localhost:3306/druid',
                    ],
                    'table'           => 'countries',
                    'keyColumn'       => 'country_iso',
                    'valueColumn'     => 'country_name',
                ],
                'injective'           => false,
                'firstCacheTimeout'   => 0,
            ],
            $lookup->toArray()
        );

        $lookup = new JdbcLookup(
            'jdbc:mysql://localhost:3306/druid',
            'myUser',
            'p4ssw0rd!',
            'countries',
            'country_iso',
            'country_name',
            "region = 'eu'",
            "updated_at",
            150,
            30,
            'PT15M',
            10
        );

        $this->assertEquals(
            [
                'type'                => 'cachedNamespace',
                'extractionNamespace' => [
                    'type'               => 'jdbc',
                    'connectorConfig'    => [
                        'connectURI' => 'jdbc:mysql://localhost:3306/druid',
                        'user'       => 'myUser',
                        'password'   => 'p4ssw0rd!',
                    ],
                    'table'              => 'countries',
                    'keyColumn'          => 'country_iso',
                    'valueColumn'        => 'country_name',
                    'filter'             => "region = 'eu'",
                    'tsColumn'           => 'updated_at',
                    'pollPeriod'         => 'PT15M',
                    'jitterSeconds'      => 150,
                    'loadTimeoutSeconds' => 30,
                    'maxHeapPercentage'  => 10,
                ],
                'injective'           => false,
                'firstCacheTimeout'   => 0,

            ],
            $lookup->toArray()
        );
    }
}
