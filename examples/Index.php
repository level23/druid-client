<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Types\Granularity;
use Level23\Druid\InputSources\InlineInputSource;

try {
    $client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

    // Enable this to see some more data
    $client->setLogger(new ConsoleLogger());

    $inputSource = new InlineInputSource(
        (string)file_get_contents(__DIR__ . '/mountains.csv')
    );

    // Build our index task
    $builder = $client->index('mountains', $inputSource)
        ->interval('2000-01-01T00:00:00.000Z/now')
        ->rollup()
        ->csvFormat([
            'Mountain',
            'Country',
            'Latitude',
            'Longitude',
            'ClimbedAt',
        ], null, null, 1)
        ->timestamp('ClimbedAt', 'dd-MM-YYYY')
        ->dimension('Mountain', 'string')
        ->dimension('Country', 'string')
        ->spatialDimension('Location', ['Latitude', 'Longitude'])
        ->segmentGranularity(Granularity::YEAR)
        ->queryGranularity(Granularity::YEAR);

    $taskId = $builder->execute();

    echo "Inserted task with id: " . $taskId . "\n";

    // Start polling task status.
    $status = $client->pollTaskStatus($taskId);

    echo "Final status: \n";
    $response = $status->data();
    unset($response['location']);

    // Display the result as a console table.
    new ConsoleTable([$response]);
} catch (Exception $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString(). "\n";
    if ($exception instanceof \GuzzleHttp\Exception\ClientException) {
        echo $exception->getResponse()->getBody() . "\n";
    }
}