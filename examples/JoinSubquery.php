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
use Level23\Druid\Context\GroupByQueryContext;

try {
    $client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

    // Enable this to see some more data
    $client->setLogger(new ConsoleLogger());

    // Build a groupBy query.
    $builder = $client->query('wikipedia')
        ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
        ->selectVirtual("timestamp_format(__time, 'yyyy-MM-dd HH:00:00')", 'hour')
        ->join(function (QueryBuilder $queryBuilder) {
            $queryBuilder
                ->select('name')
                ->sum('hits')
                ->from('anotherDataSource')
                ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
                ->where('a', '=', 'b');
        }, 'r', 'name == "r.name"')
        ->select('page', 'edited_page')
        ->select('namespace')
        ->select('r.hits', 'hits')
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
    $context = new GroupByQueryContext();
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