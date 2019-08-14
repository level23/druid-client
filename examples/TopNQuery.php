<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';

use Level23\Druid\DruidClient;

$client = new DruidClient([
    'broker_url'     => 'https://stats.moportals.com',
    'guzzle_options' => [
        'timeout'         => 30,
        'connect_timeout' => 5,
    ],
], function ($logMsg) {
    //echo "[" . $logMsg . "]\n\n";
});

$response = $client->query('sms-counters', 'all')
    ->interval(strtotime("now - 2 hours"), strtotime('tomorrow'))
    ->longSum('releases')
    ->select('destination')
    ->limit(10)
    ->orderBy('releases', 'desc')
    //->toJson();
    ->execute();

print_r($response);

//  php -f examples/GroupByQuery.php | curl -X 'POST' -H 'Content-Type:application/json' -d @- http://127.0.0.1:8888/druid/v2 | jq