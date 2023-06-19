<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Types\TimeBound;
use Level23\Druid\Filters\FilterBuilder;

try {
    $client = new DruidClient(['router_url' => 'https://127.0.0.1:8888']);

    // Enable this to see some more data
    $client->setLogger(new ConsoleLogger());

    $response = $client->metadata()->timeBoundary('wikipedia', TimeBound::MAX_TIME, function (FilterBuilder $builder) {
        $builder->where('channel', '!=', '#vi.wikipedia');
    });

    echo $response->format('d-m-Y H:i:s');
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}