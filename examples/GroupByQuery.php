<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\QueryBuilder;

$client = new DruidClient(['broker_url' => 'http://127.0.0.1:8888']);

$response = $client->query('traffic-hits')
    ->interval(new DateTime("now - 1 day"), new DateTime())
    ->select('browser')
    ->select('country_iso', 'Country')
    ->select(['mccmnc' => 'operator_code'])
    ->select('browser_version', 'browserVersie')
    ->select('mccmnc', 'carrier', new LookupExtraction('operator_title', true, 'Unknown operator'))
    ->sum('hits', 'total_hits')
    ->count('totals')
    //->distinctCount('promo_id')
    ->where('hits', '>', 1000)
    ->where('browser', 'Yandex.Browser')
    ->orWhere('browser_version', '17.4.0')
    ->orWhere(function (QueryBuilder $builder) {
        $builder->where('browser_version', '17.5.0');
        $builder->where('browser_version', '17.6.0');
    })
    ->whereIn('added', [1])
    ->limit(5)
    ->having('total_hits', '>', 100000)
    ->orderBy('total_hits', 'desc')
    ->execute(['groupByIsSingleThreaded' => false, 'sortByDimsFirst' => true]);

var_dump($response);

//  php -f examples/GroupByQuery.php | curl -X 'POST' -H 'Content-Type:application/json' -d @- http://127.0.0.1:8888/druid/v2 | jq