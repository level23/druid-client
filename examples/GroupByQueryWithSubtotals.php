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
    $builder = $client->query('wikipedia')
        ->interval('2015-09-12 20:00:00', '2015-09-12 22:00:00')
        ->selectVirtual("timestamp_format(__time, 'yyyy-MM-dd HH:00:00')", 'hour')
        ->select('namespace')
        ->count('edits')
        ->longSum('added')
        ->where('namespace', 'like', 'Draft%')
        ->subtotals([['hour', 'namespace'], ['hour'], []]);

    // Execute the query.
    $response = $builder->groupBy();

    //var_export( $response->data());

    // Display the result as a console table.
    new ConsoleTable($response->data());

} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}