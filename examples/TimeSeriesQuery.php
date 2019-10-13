<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Context\TimeSeriesQueryContext;

try {
    $client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

    // Enable this to see some more data
    //$client->setLogger(new ConsoleLogger());

    // Build a timeSeries query
    $builder = $client->query('wikipedia', 'hour')
        ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
        ->longSum('added')
        ->longSum('deleted')
        ->count('edited')
        ->select('__time', 'datetime')
        ->orderByDirection(OrderByDirection::DESC);

    // Example of setting query context. It can also be supplied as an array in the timeseries() method call.
    $context = new TimeSeriesQueryContext();
    $context->setPopulateCache(false);

    // Execute the query.
    $response = $builder->timeseries($context);

    // Display the result as a console table.
    new ConsoleTable($response->data());
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}