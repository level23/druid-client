<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;

try {
    $client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

    // Enable this to see some more data
    $client->setLogger(new ConsoleLogger());

    // Build a groupBy query.
    $builder = $client->query('mountains')
        ->interval('2000-01-01T00:00:00.000Z/now')
        ->select('Mountain')
        ->select('Country')
        ->select('Location')
        ->whereSpatialRadius('Location', [28,84], 0.8)
    ;

    // Execute the query.
    $response = $builder->execute();

    // Display the result as a console table.
    new ConsoleTable($response->data());
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}