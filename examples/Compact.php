<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';

use Level23\Druid\DruidClient;

$client = new DruidClient([
    'broker_url'      => 'http://127.0.0.1:8888',
    'coordinator_url' => 'http://127.0.0.1:8888',
    'overlord_url'    => 'http://127.0.0.1:8888',
]);

// Retrieve all intervals.
$response = $client->metadata()->intervals('traffic-conversions');

// get our first interval.
$interval = array_key_first($response);

list($start, $stop) = explode('/', $interval);

// Build our compact task.
$taskId = $client->compact('traffic-conversions')
    ->segmentGranularity('day')
    ->tuningConfig(['maxRowsInMemory' => 50000])
    ->interval($start, $stop)
    ->execute();

echo "Inserted task with id: " . $taskId . "\n";

// Start polling task status.
while (true) {
    $status = $client->taskStatus($taskId);
    echo $status['id'] . ': ' . $status['status'] . "\n";

    if ($status['status'] != 'RUNNING') {
        break;
    }
    sleep(2);
}

echo "Final status: \n";
print_r($status);