<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Context\GroupByV2QueryContext;

try {
    $client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

    // Enable this to see some more data
    $client->setLogger(new ConsoleLogger());

    // Build a groupBy query.
    $builder = $client->query('wikipedia')
        ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
        ->select('__time', 'hour', function (ExtractionBuilder $extractionBuilder) {
            $extractionBuilder->timeFormat('yyyy-MM-dd HH:00:00');
        })
        ->join(function (QueryBuilder $queryBuilder) {
            $queryBuilder
                ->from('anotherDataSource')
                ->where('a', '=', 'b');
        }, 'r', 'r.name = name')
        ->select('page', 'edited_page')
        ->select('namespace')
        ->select('r.other')
        ->count('edits')
        ->longSum('added')
        ->longSum('deleted')
        ->where('isRobot', 'false')
        ->where('channel', '!=', '#vi.wikipedia')
        ->whereIn('isNew', ['true', 'false'])
        ->Where(function (FilterBuilder $filterBuilder) {
            $filterBuilder->orWhere('namespace', 'Talk');
            $filterBuilder->orWhere('namespace', 'Main');
        })
        ->limit(10)
        ->orderBy('edits', 'desc')
        ->having('edits', '>', '5');

    // Example of setting query context. It can also be supplied as an array in the groupBy() method call.
    $context = new GroupByV2QueryContext();
    $context->setMaxOnDiskStorage(1024 * 1024);

    // Execute the query.
    $response = $builder->groupBy($context);

    // Display the result as a console table.
    new ConsoleTable($response->data());
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}