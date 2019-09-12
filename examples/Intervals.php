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

    $response = $client->metadata()->intervals('wikipedia');

    // Uncomment this to see the raw response.
    //print_r($response);


    $intervals = [];
    array_walk($response, function ($value, $key) use (&$intervals) {
        $intervals[] = array_merge($value, ['interval' => $key]);
    });

    // Display the result as a console table.
    new ConsoleTable($intervals);

} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}