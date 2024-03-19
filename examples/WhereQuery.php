<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Dimensions\DimensionBuilder;

try {
    $client = new DruidClient(['router_url' => 'https://stats.moportals.com']);

    // Enable this to see some more data
    $client->setLogger(new ConsoleLogger());

    // Build a select query
    $builder = $client->query('chat-email-events')
        ->interval('2024-02-01 00:00:00', '2024-02-23 00:00:00')
        ->selectVirtual("timestamp_format(__time, 'yyyy-MM-dd HH:00:00')", 'hour')
        ->select(['__time' => 'ts'])
        ->sum('sents')
        ->sum('views')
        ->sum('clicks')
        ->sum('bounces')
        ->where('to_domain', '=', 'gmail.com')
        ->whereNull('partnercode')
    ;

    $builder->virtualColumn('left(from_domain, 1)', 'first_letter');
    $builder->cardinality('distinct_last_name_first_char', function(DimensionBuilder $builder) {
        //$builder->select('from_domain');
        $builder->select('first_letter');
    }, true, true);


    $response = $builder->execute();

    // Display the result as a console table.
    new ConsoleTable($response->data());
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();

    if ($exception instanceof \GuzzleHttp\Exception\RequestException) {
        echo "Full body: \n" . $exception->getResponse()->getBody();
    }
}