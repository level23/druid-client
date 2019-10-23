<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Context\QueryContext;
use Level23\Druid\Types\OrderByDirection;

try {
    $client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

    // Enable this to see some more data
    //$client->setLogger(new ConsoleLogger());

    // Build a select query
    $builder = $client->query('wikipedia')
        ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
        ->select(['__time', 'channel', 'user', 'deleted', 'added'])
        ->orderByDirection(OrderByDirection::DESC)
        ->limit(10);

    // Example of setting query context. It can also be supplied as an array in the selectQuery() method call.
    $context = new QueryContext();
    $context->setPriority(100);

    // Execute the query.
    $response = $builder->selectQuery($context);

    // Display the result as a console table.
    new ConsoleTable($response->data());

    echo "Identifier for page 2: " . var_export($response->pagingIdentifier(), true) . "\n\n";

    /**
     * Now, request "page 2".
     */
    $builder->pagingIdentifier($response->pagingIdentifier());

    // Execute the query.
    $response = $builder->selectQuery($context);

    // Display the result as a console table.
    new ConsoleTable($response->data());

    echo "Identifier for page 3: " . var_export($response->pagingIdentifier(), true) . "\n\n";
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}