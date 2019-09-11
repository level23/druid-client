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

// Build our reindex task
$taskId = $client->reindex('traffic-conversions')
    ->interval('2019-04-14T00:00:00.000Z', '2019-04-15T00:00:00.000Z')
    ->segmentGranularity('day')
    ->queryGranularity('day')
    ->rollup()
    //    ->transform(function (\Level23\Druid\TransformBuilder $builder) {
    //        $builder->transform('new_age', 'age+1');
    //        $builder->where('age', '>', 16);
    //    })
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
