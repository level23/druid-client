<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Context\TopNQueryContext;

try {
    $client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

    // Enable this to see some more data
    //$client->setLogger(new ConsoleLogger());

    // Build a scan query
    $builder = $client->query('wikipedia', 'all')
        ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
        ->select('channel')
        ->count('edited')
        ->limit(10)
        ->orderBy('edited', 'desc');

    // Example of setting query context. It can also be supplied as an array in the timeseries() method call.
    $context = new TopNQueryContext();
    $context->setMinTopNThreshold(2);

    // Execute the query.
    $response = $builder->topN($context);

    // Display the result as a console table.
    new ConsoleTable($response[0]);
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}