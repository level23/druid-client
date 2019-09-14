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

    // Build our compact task.
    $taskId = $client->compact('wikipedia')
        ->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z ')
        ->segmentGranularity('day')
        ->tuningConfig(['maxRowsInMemory' => 50000])
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
    $response = $status->getResponse();
    unset($response['location']);

    // Display the result as a console table.
    new ConsoleTable([$response]);
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}