# Druid-Client

[![pipeline status](https://git.level23.nl/packages/druid-client/badges/master/pipeline.svg)](https://git.level23.nl/packages/druid-client/commits/master)
[![coverage report](https://git.level23.nl/packages/druid-client/badges/master/coverage.svg)](https://git.level23.nl/packages/druid-client/commits/master)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg?style=flat-square)](https://php.net/)


The goal of this project is to make it easy to select data from druid.

This project gives you an easy query builder to create the complex druid queries.

It also gives you a way to manage dataSources (tables) in druid and import new data from files.

## Installation

To install this package, you can use composer:

```
composer require level23/druid-client
```

## Example usage

Here is an example of how you can use this package.

Please see the inline comment for more information / feedback.

Example:

```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Extractions\ExtractionBuilder;

$client = new DruidClient(['broker_url' => 'https://broker.url']);

$response = $client->query('traffic-hits')
    // REQUIRED: you have to select the interval where to select the data from.
    ->interval('now - 1 day', 'now')
    // Simple dimension select
    ->select('browser')
    // Select a dimension with a different output name.
    ->select('country_iso', 'Country')
    // Alternative way to select a dimension with a different output name. 
    // If you want, you can select multiple dimensions at once.
    ->select(['mccmnc' => 'operator_code'])
    // Select a dimension, but change it's value using a lookup function.
    ->lookup('operator_title', 'mccmnc', 'carrier', 'Unknown')
    // Select a dimension, but change it's value by using an extraction function. Multiple functions are available,
    // like timeFormat, upper, lower, substring, lookup, regexp, etc.
    ->select('__time', 'datetime', function( ExtractionBuilder $builder) {
        $builder->timeFormat('yyyy-MM-dd HH:00:00');
    })    
    // Summing a metric.
    ->sum('hits', 'total_hits')
    // Count the total number of rows (per the dimensions selected) and store it in totalNrRecords.
    ->count('totalNrRecords')
    // Count the number of dimensions. NOTE: Theta Sketch extension is required to run this aggregation.
    ->distinctCount('browser', 'numberOfBrowsers')
    // Build some filters.
    ->where('hits', '>', 1000)
    // When no operator is given, we assume an equals (=)
    ->where('browser', 'Yandex.Browser')
    ->orWhere('browser_version', '17.4.0')
    // Where filters using Closure's are supported.
    ->orWhere(function (FilterBuilder $builder) {
        $builder->where('browser_version', '17.5.0');
        $builder->where('browser_version', '17.6.0');
    })
    // Filter using an IN filter.
    ->whereIn('video_id', [1, 152, 919])
    // Filter using a between filter. It's an inclusive filter, like "age >= 18 and age <= 99".   
    ->whereBetween('age', 18, 99)
    // Limit the number of results.
    ->limit(5)
    // Apply a having filter, this is applied after selecting the records. 
    ->having('total_hits', '>', 100)
    // Sort the results by this metric/dimension
    ->orderBy('total_hits', 'desc')
    // Execute the query. Optionally you can specify Query Context parameters.
    ->execute(['groupByIsSingleThreaded' => false, 'sortByDimsFirst' => true]);
```

## Dimension selections

Dimensions are fields where you normally filter on, or _Group_ data by. Typical examples are: Country, Name, City, etc.

To select a _dimension_, you can use one of the methods below:

#### `select()`

This method has the following arguments:

| **Type**        | **Argument**  | **Example**                        | **Description**                                                                                                                                                    |
|-----------------|---------------|------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string or array | `$dimension`  | country_iso                        |  The dimension which you want to select                                                                                                                            |
| string          | `$as`         | country                            | The name where the result will be available by in the result set.                                                                                                  |
| Closure         | `$extraction` | A PHP closure, see example below.  | A PHP Closure function. This function will receive an instance of the ExtractionBuilder, which allows you to extract data from the dimension as you would like it. |
| string          | `$outputType` | string                             | The output type of the data. If left unspecified, we will use `string`.                                                                                            |

This method allows you to select a dimension in various way's, as shown in the example above. 

You can use:

**Simple dimension selection:**
```php 
->select("country_iso")
```

**Dimension selection with an alternative output name:**
```php 
->select("country_iso", "Country")
```

**Select various dimensions at once:**
```php 
->select(["browser", "country_iso", "age", "gender"])
```

**Select various dimensions with alternative output names at once:**
```php 
->select([
    "browser"     => "TheBrowser", 
    "country_iso" => "CountryIso", 
    "age"         => "Age",
    "gender"      => "MaleOrFemale"
])
```

**Select a dimension and extract a value from it:**
```php 
// retrieve the first two characters from the "locale" string and use it as language.
->select("locale", "language", function(ExtractionBuilder $extraction) {
    $extraction->substring(0, 2);
})
```

See the chapter __Extractions__ for all available extractions.

**Change the output type of a dimension:**
```php 
->select("age", null, null, "long")
```

#### `lookup()`

This method allows you to lookup a dimension using a registered lookup function. See more about registered lookup
functions on these pages:

* https://druid.apache.org/docs/latest/querying/lookups.html
* https://druid.apache.org/docs/latest/development/extensions-core/lookups-cached-global.html

Lookup's are a handy way to transform an ID value into a user readable name, like transforming a `user_id` into the
`username`, without having to store the username in your dataset. 

This method has the following arguments:

| **Type**       | **Argument**           | **Example**    | **Description**                                                                                                                                                                                                                                                      |
|----------------|------------------------|----------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string         | `$lookupFunction`      | username_by_id | The name of the lookup function which you want to use for this dimension.                                                                                                                                                                                            |
| string         | `$dimension`           | user_id        | The dimension which you want to transform.                                                                                                                                                                                                                           |
| string         | `$as`                  | username       | The name where the result will be available by in the result set.                                                                                                                                                                                                    |
| bool or string | `$replaceMissingValue` | Unknown        | When the user_id dimension could not be found, what do you want to do? Use `false` for remove the value from the result, use `true` to keep the original dimension value (the user_id). Or, when a string is given, we will replace the value with the given string. |


## Metric Aggregations

Metrics are fields which you normally aggregate, like summing the values of this field, Typical examples are:
- Revenue
- Hits
- NrOfTimes Clicked / Watched / Bought
- Conversions

To aggregate a metric, you can use one of the methods below:

#### `count()`

See: https://druid.apache.org/docs/latest/querying/aggregations.html#count-aggregator

#### `sum()`

Alternatives are: `longSum()`, `doubleSum()` and `floatSum()`, which allow you to directly specify the output type by
using the appropriate method name. 

See: https://druid.apache.org/docs/latest/querying/aggregations.html#sum-aggregators

#### `min()`

Alternatives are: `longMin()`, `doubleMin()` and `floatMin()`, which allow you to directly specify the output type by
using the appropriate method name. 

See: https://druid.apache.org/docs/latest/querying/aggregations.html#min-max-aggregators

#### `max()`

Alternatives are: `longMax()`, `doubleMax()` and `floatMax()`, which allow you to directly specify the output type by
using the appropriate method name.

See: https://druid.apache.org/docs/latest/querying/aggregations.html#min-max-aggregators 

#### `first()`

Alternatives are: `longFirst()`, `doubleFirst()`, `floatFirst()` and `stringFirst()`, which allow you to directly specify the output type by
using the appropriate method name.

See: https://druid.apache.org/docs/latest/querying/aggregations.html#first-last-aggregator 

#### `last()`

Alternatives are: `longLast()`, `doubleLast()`, `floatLast()` and `stringLast()`, which allow you to directly specify the output type by
using the appropriate method name.

See: https://druid.apache.org/docs/latest/querying/aggregations.html#first-last-aggregator 

#### `javascript()`

See: https://druid.apache.org/docs/latest/querying/aggregations.html#javascript-aggregator

More info to come!

For testing/building, run:
```
infection --threads=4 --only-covered

ant phpstan
```
