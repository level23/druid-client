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
    //$client->setLogger(new ConsoleLogger());

    // Retrieve all intervals.
    $response = $client->metadata()->intervals('wikipedia');

    // get our first interval.
    $interval = array_key_first($response);

    // Build our compact task.
    $response = $client->query('wikipedia')
        ->interval($interval)
        ->segmentMetadata();


    // Display the result as a console table.
    new ConsoleTable($response->getResponse());

    // Uncomment this to see the raw response
    //print_r($response->getRawResponse());
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}