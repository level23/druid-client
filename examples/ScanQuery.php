<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';

use Level23\Druid\DruidClient;

$client = new DruidClient([
    'broker_url'      => 'http://127.0.0.1:8888',
    'coordinator_url' => 'http://127.0.0.1:8888',
    'overlord_url'    => 'http://127.0.0.1:8888',
]);

$response = $client->query('my_counters')
    ->interval('now - 2 hours', 'now')
    ->select(['__time', 'number_id', 'country_id', 'range', 'messages', 'releases'])
    ->limit(3)
    ->scan([]);

print_r($response);
