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

// Retrieve all intervals.
$response = $client->metadata()->intervals('traffic-conversions');

// get our first interval.
$interval = array_key_first($response);

// Build our compact task.
$structure = $client->query('traffic-conversions')
    ->interval($interval)
    ->segmentMetadata();

print_r($structure);