# Druid-Client

[![pipeline status](https://git.level23.nl/packages/druid-client/badges/master/pipeline.svg)](https://git.level23.nl/packages/druid-client/commits/master)
[![coverage report](https://git.level23.nl/packages/druid-client/badges/master/coverage.svg)](https://git.level23.nl/packages/druid-client/commits/master)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg?style=flat-square)](https://php.net/)


The goal of this project is to make it easy to select data from druid.

This project gives you an easy query builder to create the complex druid queries.

Example:

```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;
use Level23\Druid\DruidClient;
use Level23\Druid\QueryBuilder;

$client = new DruidClient(['broker_url' => 'http://127.0.0.1:8888']);

$response = $client->query('traffic-hits')
    ->interval(new DateTime("now - 1 day"), new DateTime())
    ->select('browser')
    ->select('country_iso', 'Country')
    ->select(['mccmnc' => 'operator_code'])
    ->select('browser_version', 'browserVersie')
    ->select('mccmnc', 'carrier', 'operator_title', true, 'Unknown operator')
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

```

More info to come!

For testing/building, run:
```
infection --threads=4

ant phpstan
```
