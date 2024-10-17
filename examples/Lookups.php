<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/helpers/ConsoleLogger.php';
include __DIR__ . '/helpers/ConsoleTable.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Types\OrderByDirection;

try {
    $client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

    // Enable this to see some more data
    //$client->setLogger(new ConsoleLogger());

    // Store a lookup.
    $client->lookup()
        ->map([
            'nl' => 'The Netherlands',
            'de' => 'Germany',
            'be' => 'Belgium',
            'fr' => 'France',
            'it' => 'Italy',
            'es' => 'Spain',
        ])->store('country_iso_to_name');

    // List al lookup names
    $names = $client->lookup()->names();

    // Display the result as a console table.
    new ConsoleTable(array_map(fn($name) => ["lookup name" => $name], $names));

} catch (Throwable $exception) {
    echo "Something went wrong during retrieving druid data\n";
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString();
}