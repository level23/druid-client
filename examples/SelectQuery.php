<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';

use Level23\Druid\DruidClient;

$client = new DruidClient([
    'broker_url'      => 'http://stats.moportals.com',
    'coordinator_url' => 'http://stats.moportals.com',
    'overlord_url'    => 'http://stats.moportals.com',
]);

echo "Start query";
$response = $client->query('sms-counters', 'all')
    ->interval('now - 1 day', 'now')
    ->limit(2)
    ->selectQuery();

$identifier = $client->getPagingIdentifier();
print_r($response);
print_r($identifier);

$response = $client->query('sms-counters', 'all')
    ->interval('now - 1 day', 'now')
    ->limit(2)
    ->pagingIdentifier($identifier)
    ->selectQuery();

$identifier = $client->getPagingIdentifier();
print_r($response);
print_r($identifier);
