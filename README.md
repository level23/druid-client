# Druid-Client

[![pipeline status](https://git.level23.nl/packages/druid-client/badges/master/pipeline.svg)](https://git.level23.nl/packages/druid-client/commits/master)
[![coverage report](https://git.level23.nl/packages/druid-client/badges/master/coverage.svg)](https://git.level23.nl/packages/druid-client/commits/master)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg?style=flat-square)](https://php.net/)


The goal of this project is to make it easy to select data from druid.

This project gives you an easy query builder to create the complex druid queries.

It also gives you a way to manage dataSources (tables) in druid and import new data from files.

## Requirements

This package only requires Guzzle from version 4 or higher. 

It requires PHP version 7.2 or higher. 


## Installation

To install this package, you can use composer:

```
composer require level23/druid-client
```

You can also download it as a ZIP file and include it in your project, as long as you have guzzle also in your project.

## Laravel/Lumen support.

This package is Laravel/Lumen ready.  It can be used in a Laravel/Lumen project, but its not required.

#### Laravel

For Laravel 5.6+ the package will be auto discovered. For Laravel <= 5.5 you should add the service provider 
`Level23\Druid\DruidServiceProvider::class` in the file `config/app.php`.

#### Lumen 

If you are using a Lumen project, just include the service provider
in `bootstrap/app.php`:
```php
// Register the druid-client service provider
$app->register(Level23\Druid\DruidServiceProvider::class);
```

#### Configuration:

You should also define the correct endpoint url's in your `.env` in your Laravel/Lumen project:
```
DRUID_BROKER_URL=http://broker.url:8082
DRUID_COORDINATOR_URL=http://coordinator.url:8081
DRUID_OVERLORD_URL=http://overlord.url:8090
DRUID_RETRIES=2
DRUID_RETRY_DELAY_MS=500
```

If you are using a Druid Router process, you can also just set the router url, which then will used for the broker,
overlord and the coordinator:
```
DRUID_ROUTER_URL=http://druid-router.url:8080
```

## Todo's

 - Implement Kill Task
 - Support for subtotalsSpec in GroupBy query
 - Support for building metricSpec and DimensionSpec in CompactTaskBuilder
 - metrics selection for select query (currently all columns are returned)
 - whereColumn filter

## Examples

There are several examples which are written on the single-server tutorial of druid. 
See [this](examples/README.md) page for more information.

## Documentation

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

$client = new DruidClient(['router_url' => 'https://router.url:8080']);

$response = $client->query('traffic-hits', 'all')
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

## Filters

With filters you can filter on certain values. The following filters are available:

#### `where()`

This is probably the most used filter. It is very flexible.

This method uses the following arguments:


| **Argument** | **Type** | **Example**        |
|--------------|----------|--------------------|
| $dimension   | string   | "cityName"         |
| $operator    | string   | "="                |
| $value       | mixed    | "Auburn"           |
| $extraction  | Closure  | See example below. |
| $boolean     | string   | "and" / "or"       |

The following `$operator` values are supported:

| **Operator**   | **Description**                                                                                                                                                                                |
|----------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| =              | Check if the dimension is equal to the given value.                                                                                                                                            |
| !=             | Check if the dimension is not equal to the given value.                                                                                                                                        |
| <>             | Same as `!=`                                                                                                                                                                                   |
| >              | Check if the dimension is greater than the given value.                                                                                                                                        |
| >=             | Check if the dimension is greater than or equal to the given value.                                                                                                                            |
| <              | Check if the dimension is less than the given value.                                                                                                                                           |
| <=             | Check if the dimension is less than or equal to the given value.                                                                                                                               |
| like           | Check if the dimension matches a SQL LIKE expression. Special characters supported are "%" (matches any number of characters) and "_" (matches any one character).                             |
| not like       | Same as `like`, only now the dimension should not match.                                                                                                                                       |
| javascript     | Check if the dimension matches by using the given javascript function. The function takes a single argument, the dimension value, and returns either true or false.                            |
| not javascript | Same as `javascript`, only now the dimension should not match.                                                                                                                                 |
| regex          | Check if the dimension matches the given regular expression.                                                                                                                                   |
| not regex      | Check if the dimension does not match the given regular expression.                                                                                                                            |
| search         | Check if the dimension partially matches the given string(s). When an array of values are given, we expect the dimension value contains all of the values specified in this search query spec. |
| not search     | Same as `search`, only now the dimension should not match.                                                                                                                                     | 

We support retrieving a value using an extraction function. This can be done by passing a `Closure` function in the 
`$extraction` parameter. This function will receive a `ExtractionBuilder`, which allows you to extract the value which 
you want.

For example:
```php

// Build a groupby query.
$builder = $client->query('wikipedia')
    // Filter on all names starting with "jo"    
    ->where('name', '=', 'jo' function (ExtractionBuilder $extractionBuilder) {
        $extractionBuilder->substring(2);
    })
```
For a full list of extraction functions, see the Extractions chapter


This method supports a quick equals shorthand. Example:
```php
$builder->where('name', 'John');
```
Is the same as
```php
$builder->where('name', '=', 'John');
```


We also support using a Closure to group various filters in 1 filter. For example:
```php
$builder->where(function (FilterBuilder $filterBuilder) {
    $filterBuilder->orWhere('namespace', 'Talk');
    $filterBuilder->orWhere('namespace', 'Main');
});
$builder->where('channel', 'en');
```

This would be the same as an SQL equivalent:
```SELECT ... WHERE (namespace = 'Talk' OR 'namespace' = 'Main') AND 'channel' = 'en'; ``` 


#### `orWhere()`

Same as where, but now we will join previous added filters with a `or` instead of an `and`.

#### `whereIn()`

With this method you can filter on records using multiple values. 

This method has the following arguments:

| **Type** | **Argument**  | **Example**        | **Description**                                                                |
|----------|---------------|--------------------|--------------------------------------------------------------------------------|
| string   | `$dimension`  | country_iso        | The dimension which you want to filter                                         |
| array    | `$items`      | ["it", "de", "au"] | A list of values. We will return records where the dimension is in this list.  |
| Closure  | `$extraction` | See Extractions    | An extraction function to extract a different value from the dimension.        |

#### `whereNotIn()`

This works the same as `whereIn()`, only now we will check if the dimension is NOT in the given values. See `whereIn()` 
for more details.  

#### `whereBetween()`

@todo

#### `whereNotBetween()`

@todo

#### `whereInterval()`

@todo

#### `whereNotInterval()`

@todo

## Extractions

@todo


## MISC

More info to come!

For testing/building, run:
```
infection --threads=4 --only-covered

ant phpstan
```