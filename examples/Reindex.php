<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Types\Granularity;
use Level23\Druid\InputSources\DruidInputSource;

try {
    $client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

    // Enable this to see some more data
    $client->setLogger(new ConsoleLogger());

    # Build our input source for the reindex job.
    # Only read the non-draft records.
    $source = new DruidInputSource('wikipedia');
    $source->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z');
    $source->where('namespace', 'not like', '%Draft%');

    # Build our reindex task
    $taskId = $client->reindex('wikipedia')
        ->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z')
        ->parallel()
        ->inputSource($source) # Specify our custom input source.
        ->segmentGranularity(Granularity::DAY)
        ->queryGranularity(Granularity::HOUR)
        //        ->rollup()
        //        ->transform(function (\Level23\Druid\Transforms\TransformBuilder $builder) {
        //            $builder->transform('"true"', 'isRobot');
        //            $builder->where('comment', 'like', '%Robot%');
        //        })
        ->execute();

    echo "Inserted task with id: " . $taskId . "\n";

    // Start polling task status.
    while (true) {
        $status = $client->taskStatus($taskId);
        echo $status->getId() . ': ' . $status->getStatus() . "\n";

        if ($status->getStatus() != 'RUNNING') {
            break;
        }
        sleep(2);
    }

    echo "Final status: \n";
    $response = $status->data();
    unset($response['location']);

    // Display the result as a console table.
    new ConsoleTable([$response]);
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}