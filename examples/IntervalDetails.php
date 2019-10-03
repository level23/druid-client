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

    $response = $client->metadata()->interval('wikipedia', '2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z');

    // Uncomment this to see the raw response.
    var_export($response);

} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}