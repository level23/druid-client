<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Types\OrderByDirection;

try {
    $client = new DruidClient(['router_url' => 'http://23.88.108.155:8081']);
    //$client = new DruidClient(['router_url' => 'https://stats.moportals.com']);

    // Enable this to see some more data
    //$client->setLogger(new ConsoleLogger());

    //    $response = $client->lookups();
    //
    //    $names = $client->lookup()->names();
    //    var_export($names);
    //
    //    $tiers = $client->lookup()->tiers();
    //    var_export($tiers);
    //
    //    $response = $client->lookup()->get('mccmnc_name');
    //    var_export($response);

    //var_export($client->lookup()->keys('mccmnc_name'));

//    $client->lookup()
//        ->map([
//            '1'   => 1.25,
//            2     => true,
//            3     => false,
//            4     => 'hoi',
//            5     => 5,
//            6     => null,
//            7     => ['a', 'b'],
//            true  => 8,
//            false => 9,
//            0     => 10,
//            1.1   => 11,
//            null  => 12,
//        ])
//        ->store('test');

    var_export($client->lookup()->get('test_map'));
//    var_export($client->lookup()->values('test'));
//    var_export($client->lookup()->introspect('test'));

    //    $client->lookup()
    //        ->uri('https://www.ttest.nl/feed.json', 'PT15M', 10)
    //        ->store('id_country');
    //
    //    $client->lookup()
    //        ->kafka('')
    //        ->store('id_country');
    //
    //    $client
    //        ->lookup()
    //        ->jdbc(
    //            connectUri: 'jdbc:mysql://db.gateway-ro.moportals.com:3306/vpsmobile',
    //            username: 'druid_hetzner',
    //            password: '<pwd>',
    //            table: 'promoter',
    //            keyColumn: 'id',
    //            valueColumn: 'username',
    //            filter: 'username != "test"',
    //            pollPeriod: 'P15M',
    //            jitterSeconds: 300
    //        )
    //        ->injective()
    //        ->firstCacheTimeout(200)
    //        ->store('usernames');

    //    $response = $client->lookup()->get('usernames');
    //    var_export($response);
    //
    //    $client->lookup()->delete('usernames');

    // Display the result as a console table.
    //    new ConsoleTable($names);
} catch (Throwable $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}