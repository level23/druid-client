<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';

use Level23\Druid\DruidClient;

$client = new DruidClient([
    'broker_url'     => 'https://stats.moportals.com',
    'guzzle_options' => [
        'timeout'         => 5,
        'connect_timeout' => 5,
    ],
], function ($logMsg) {
    //echo "[" . $logMsg . "]\n\n";
});

$response = $client->query('sms-counters', 'hour')
    ->interval(strtotime("now - 2 hours"), strtotime('tomorrow'))
    ->longSum('releases')
    ->longSum('messages')
    ->doubleSum('reward_eur')
    ->longSum('app_messages')
    ->longSum('unidentified_messages')
    ->select('__time', 'datetime')
    //->toJson();
    ->execute();

print_r($response);

//  php -f examples/GroupByQuery.php | curl -X 'POST' -H 'Content-Type:application/json' -d @- http://127.0.0.1:8888/druid/v2 | jq