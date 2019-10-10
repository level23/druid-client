# Druid-Client

[![Build Status](https://travis-ci.org/level23/druid-client.svg?branch=master)](https://travis-ci.org/level23/druid-client)
[![Coverage Status](https://coveralls.io/repos/github/level23/druid-client/badge.svg?branch=master)](https://coveralls.io/github/level23/druid-client?branch=master)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg?style=flat-square)](https://php.net/)


The goal of this project is to make it easy to select data from druid.

This project gives you an easy query builder to create the complex druid queries.

It also gives you a way to manage dataSources (tables) in druid and import new data from files.

## Requirements

This package only requires Guzzle from version 6.2 or higher. 

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

#### Laravel/Lumen Configuration:

You should also define the correct endpoint url's in your `.env` in your Laravel/Lumen project:
```
DRUID_BROKER_URL=http://broker.url:8082
DRUID_COORDINATOR_URL=http://coordinator.url:8081
DRUID_OVERLORD_URL=http://overlord.url:8090
DRUID_RETRIES=2
DRUID_RETRY_DELAY_MS=500
DRUID_TIMEOUT=60
DRUID_CONNECT_TIMEOUT=10
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
 - Metrics selection for select query (currently all columns are returned)
 - WhereColumn filter
 - Implement SearchQuery: https://druid.apache.org/docs/latest/querying/searchquery.html
 - Implement index_parallel
 - Implement support for Spatial filters

## Examples

There are several examples which are written on the single-server tutorial of druid. 
See [this](examples/README.md) page for more information.

## Documentation

Here is an example of how you can use this package.

**NOTE**: This documentation is still under development. Feel free to give feedback.

Please see the inline comment for more information / feedback.

Example:

```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

include __DIR__ . '/../vendor/autoload.php';

use Level23\Druid\DruidClient;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Extractions\ExtractionBuilder;

$client = new DruidClient(['router_url' => 'https://router.url:8080']);

$response = $client->query('traffic-hits', Granularity::ALL)
    // REQUIRED: you have to select the interval where to select the data from.
    ->interval('now - 1 day', 'now')
    // Simple dimension select
    ->select('browser')
    // Select a dimension with a different output name.
    ->select('country_iso', 'Country')
    // Alternative way to select a dimension with a different output name. 
    // If you want, you can select multiple dimensions at once.
    ->select(['mccmnc' => 'carrierCode'])
    // Select a dimension, but change it's value using a lookup function.
    ->lookup('carrier_title', 'mccmnc', 'carrierName', 'Unknown')
    // Select a dimension, but change it's value by using an extraction function. Multiple functions are available,
    // like timeFormat, upper, lower, substring, lookup, regexp, etc.
    ->select('__time', 'dateTime', function( ExtractionBuilder $builder) {
        $builder->timeFormat('yyyy-MM-dd HH:00:00');
    })    
    // Summing a metric.
    ->sum('hits', 'totalHits')
    // Sum hits which only occurred at night
    ->sum('hits', 'totalHitsNight', function(FilterBuilder $filter) {
        $filter->whereInterval('__time', ['yesterday 20:00/today 6:00']); 
    })
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
    ->having('totalHits', '>', 100)
    // Sort the results by this metric/dimension
    ->orderBy('totalHits', 'desc')
    // Execute the query. Optionally you can specify Query Context parameters.
    ->execute(['groupByIsSingleThreaded' => false, 'sortByDimsFirst' => true]);
```

## DruidClient

The `DruidClient` class is the class where it all begins. You initiate an instance of the druid client, which holds the
configuration of your instance.

The `DruidClient` constructor has the following arguments:

| **Type**            | **Optional/Required** | **Argument** | **Example**                         | **Description**                                                                                                                         |
|---------------------|-----------------------|--------------|-------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------|
| array               | Required              | `$config`    | `['router_url' => 'http://my.url']` | The configuration which is used for this DruidClient. This configuration contains the endpoints where we should send druid queries to.  |
| `GuzzleHttp\Client` | Optional              | `$client`    | See example below                   | If given, we will this Guzzle Client for sending queries to your druid instance. This allows you to control the connection.             |  

By default we will use a guzzle client for handing the connection between your application and the druid server. 
If you want to change this, for example because you want to use a proxy, you can do this with a custom guzzle client.

Example of using a custom guzzle client:
```php

// Create a custom guzzle client which uses an http proxy.
$guzzleClient = new GuzzleHttp\Client([
    'proxy' => 'tcp://localhost:8125',
    'timeout' => 30,
    'connect_timeout' => 10
]);

// Create a new DruidClient, which uses our custom Guzzle Client 
$druidClient = new DruidClient(
    ['router_url' => 'http://druid.router.com'], 
    $guzzleClient
);

// Query stuff here.... 
```  

The `DruidClient` class gives you various methods. The most commonly used is the `query()` method, which allows you
to build and execute a query.

#### `DruidClient::query()`

The `query()` method gives you a `QueryBuilder` instance, which allows you to build a query and then execute it. 

Example:
```php
$client = new DruidClient(['router_url' => 'https://router.url:8080']);

// retrieve our query builder, group the results per day.
$builder = $client->query('wikipedia', Granularity::DAY);

// Now build your query ....
// $builder->select( ... )->where( ... )->interval( ... );  
```

The query method has 2 parameters: 

| **Type** | **Optional/Required** | **Argument**   | **Example** | **Description**                                                                                                                                                                                                                                                                                                                                                      |
|----------|-----------------------|----------------|-------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dataSource`  | "wikipedia" | The name of the dataSource (table) which you want to query.                                                                                                                                                                                                                                                                                                          |
| string   | Optional              | `$granularity` | "all"       | The granularity which you want to use for this query. You can think of this like an extra "group by" per time window. The results will be grouped by this time window. By default we will use "all", which will return the resultSet in 1 set. Valid values are: all, none, second, minute, fifteen_minute, thirty_minute, hour, day, week, month, quarter and year  |

The QueryBuilder allows you to select dimensions, aggregate metric data, apply filters and having filters, etc.

See the following chapters for more information about the query builder.  

  - [Generic query methods](#generic-query-methods)
  - [Dimension selections](#dimension-selections)
  - [Metric aggregations](#metric-aggregations)
  - [Filters](#filters)
  - [Extractions](#extractions)
  - [Having](#having)
  - [Virtual Columns](#virtual-columns)
  - [Post Aggregations](#post-aggregations)
  
## Generic query methods

Here we will describe some methods which are generic and can be used by (almost) all queries. 

#### `interval()`

Because Druid is a TimeSeries database, you always need to specify between which times you want to query. With this method
you can do just that. 

The interval method is very flexible and supports various argument formats. 

All these examples are valid:

```php
// Select an interval with string values. Anything which can be parsed by the DateTime object
// can be given. Also "yesterday" or "now" is valid.
$builder->interval('2019-12-23', '2019-12-24');

// When a string is given which contains a slash, we will split it for you and parse it as "begin/end".
$builder->interval('yesterday/now');

// An "raw" interval as druid uses them is also allowed
$builder->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z');

// You can also give DateTime objects
$builder->interval(new DateTime('yesterday'), new DateTime('now'));

// Carbon is also supported, as it extends DateTime
$builder->interval(Carbon::now()->subDay(), Carbon::now());

// Timestamps are also supported:
$builder->interval(1570643085, 1570729485);
```

The start date should be before the end date. If not, an `InvalidArgumentException` will be thrown.

The `interval()` method has the following parameters:

| **Type**                  | **Optional/Required** | **Argument** | **Example**      | **Description**                                                                                                                                                                    |
|---------------------------|-----------------------|--------------|------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string/int/DateTime       | Required              | `$start`     | "now - 24 hours" | The start date from where we will query. See the examples above which formats are allowed.                                                                                         |
| /string/int/DateTime/null | Optional              | `$stop`      | "now"            | The stop date from where we will query. See the examples above which formats are allowed. When a string containing a slash is given as start date, the stop date can be left out.  | 
  
#### `limit()`

@todo

#### `orderBy()`

@todo

## Dimension selections

Dimensions are fields where you normally filter on, or _Group_ data by. Typical examples are: Country, Name, City, etc.

To select a _dimension_, you can use one of the methods below:

#### `select()`

This method has the following arguments:

| **Type**        | **Optional/Required** | **Argument**  | **Example**                        | **Description**                                                                                                                                                    |
|-----------------|-----------------------|---------------|------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string or array | Required              | `$dimension`  | country_iso                        |  The dimension which you want to select                                                                                                                            |
| string          | Optional              | `$as`         | country                            | The name where the result will be available by in the result set.                                                                                                  |
| Closure         | Optional              | `$extraction` | A PHP closure, see example below.  | A PHP Closure function. This function will receive an instance of the ExtractionBuilder, which allows you to extract data from the dimension as you would like it. |
| string          | Optional              | `$outputType` | string                             | The output type of the data. If left unspecified, we will use `string`.                                                                                            |

This method allows you to select a dimension in various way's, as shown in the example above. 

You can use:

**Simple dimension selection:**
```php 
$builder->select('country_iso');
```

**Dimension selection with an alternative output name:**
```php 
$builder->select('country_iso', 'Country');
```

**Select various dimensions at once:**
```php 
$builder->select(['browser', 'country_iso', 'age', 'gender']);
```

**Select various dimensions with alternative output names at once:**
```php 
$builder->select([
    'browser'     => 'TheBrowser', 
    'country_iso' => 'CountryIso', 
    'age'         => 'Age',
    'gender'      => 'MaleOrFemale'
])
```

**Select a dimension and extract a value from it:**
```php 
// retrieve the first two characters from the "locale" string and use it as language.
$builder->select("locale", "language", function(ExtractionBuilder $extraction) {
    $extraction->substring(0, 2);
});
```

See the chapter __Extractions__ for all available extractions.

**Change the output type of a dimension:**
```php 
$builder->select('age', null, null, 'long');
```

#### `lookup()`

This method allows you to lookup a dimension using a registered lookup function. See more about registered lookup
functions on these pages:

* https://druid.apache.org/docs/latest/querying/lookups.html
* https://druid.apache.org/docs/latest/development/extensions-core/lookups-cached-global.html

Lookup's are a handy way to transform an ID value into a user readable name, like transforming a `user_id` into the
`username`, without having to store the username in your dataset. 

This method has the following arguments:

| **Type**       | **Optional/Required** | **Argument**           | **Example**    | **Description**                                                                                                                                                                                                                                                      |
|----------------|-----------------------|------------------------|----------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string         | Required              | `$lookupFunction`      | username_by_id | The name of the lookup function which you want to use for this dimension.                                                                                                                                                                                            |
| string         | Required              | `$dimension`           | user_id        | The dimension which you want to transform.                                                                                                                                                                                                                           |
| string         | Optional              | `$as`                  | username       | The name where the result will be available by in the result set.                                                                                                                                                                                                    |
| bool or string | Optional              | `$replaceMissingValue` | Unknown        | When the user_id dimension could not be found, what do you want to do? Use `false` for remove the value from the result, use `true` to keep the original dimension value (the user_id). Or, when a string is given, we will replace the value with the given string. |

Example:
```php
$builder->lookup('lookupUsername', 'user_id', 'username', 'Unknown'); 
```

## Metric Aggregations

Metrics are fields which you normally aggregate, like summing the values of this field, Typical examples are:
- Revenue
- Hits
- NrOfTimes Clicked / Watched / Bought
- Conversions
- PageViews
- Counters

To aggregate a metric, you can use one of the methods below. 

All of the metrics support a filter selection. If this is given, the metric aggregation will only be applied to the 
records where the filters match.  

Example:

```php
// count how many page views are done by kids
$builder->longSum('pageViews', 'pageViewsByKids', function(FilterBuilder $filter) {
    $filter->where('age', '<=', 16); 
});
```

See also this page: https://druid.apache.org/docs/latest/querying/aggregations.html
  
This method uses the following arguments:

#### `count()`

This aggregation will return the number of rows which match the filters.

Please note the count aggregator counts the number of Druid rows, which does not always reflect the number of raw 
events ingested. This is because Druid can be configured to roll up data at ingestion time. To count the number 
of ingested rows of data, include a count aggregator at ingestion time, and a longSum aggregator at query time.

Example:

```php
$builder->count('nrOfResults');
```

| **Type** | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                         |
|----------|-----------------------|------------------|----------------------------------------------|-------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$as`            | "nrOfRows"                                   | The size of the bucket where the numerical values are grouped in                                                        |
| Closure  | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only count the records which match with the given filter. |


#### `sum()`

The `sum()` aggregation computes the sum of values as a 64-bit, signed integer.

**Note:** Alternatives are: `longSum()`, `doubleSum()` and `floatSum()`, which allow you to directly specify the output type by
using the appropriate method name. These methods do not have the `$type` parameter.

Example:

```php
$builder->sum('views', 'totalViews');
```

The `sum()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                       |
|----------|-----------------------|------------------|----------------------------------------------|-----------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$metric`        | "views"                                      | The metric which you want to sum                                                                                      |
| string   | Optional              | `$as`            | "totalViews"                                 | The name which will be used in the output result                                                                      |
| string   | Optional              | `$type`          | "long"                                       | The output type of the sum. This can either be long, float or double.                                                 |
| Closure  | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only sum the records which match with the given filter. |

#### `min()`

The `min()` aggregation computes the minimum of all metric values.

**Note:** Alternatives are: `longMin()`, `doubleMin()` and `floatMin()`, which allow you to directly specify the output type by
using the appropriate method name.  These methods do not have the `$type` parameter.

Example:
```php
$builder->min('age', 'minAge');
```

The `min()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                                  |
|----------|-----------------------|------------------|----------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$metric`        | "views"                                      | The metric which you want to calculate the minimum value of.                                                                                     |
| string   | Optional              | `$as`            | "totalViews"                                 | The name which will be used in the output result                                                                                                 |
| string   | Optional              | `$type`          | "long"                                       | The output type. This can either be long, float or double.                                                                                       |
| Closure  | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only calculate the minimum value of the records which match with the given filter. |


#### `max()`

The `max()` aggregation computes the maximum of all metric values.

**Note:** Alternatives are: `longMax()`, `doubleMax()` and `floatMax()`, which allow you to directly specify the output type by
using the appropriate method name.  These methods do not have the `$type` parameter.

Example:
```php
$builder->max('age', 'maxAge');
```

The `max()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                                  |
|----------|-----------------------|------------------|----------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$metric`        | "views"                                      | The metric which you want to calculate the maximum value of.                                                                                     |
| string   | Optional              | `$as`            | "totalViews"                                 | The name which will be used in the output result                                                                                                 |
| string   | Optional              | `$type`          | "long"                                       | The output type. This can either be long, float or double.                                                                                       |
| Closure  | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only calculate the maximum value of the records which match with the given filter. |


#### `first()`

The `first()` aggregation computes the metric value with the minimum timestamp or 0 if no row exist.

**Note:** Alternatives are: `longFirst()`, `doubleFirst()`, `floatFirst()` and `stringFirst()`, which allow you to 
directly specify the output type by using the appropriate method name.  These methods do not have the `$type` parameter.

Example:
```php
$builder->first('device')
```

The `first()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                              |
|----------|-----------------------|------------------|----------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$metric`        | "device"                                     | The metric which you want to compute the first value of.                                                                                     |
| string   | Optional              | `$as`            | "firstDevice"                                | The name which will be used in the output result                                                                                             |
| string   | Optional              | `$type`          | "long"                                       | The output type. This can either be string, long, float or double.                                                                           |
| Closure  | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only compute the first value of the records which match with the given filter. |


#### `last()`

The `last()` aggregation computes the metric value with the maximum timestamp or 0 if no row exist.

Note that queries with last aggregators on a segment created with rollup enabled will return the rolled up value, 
and not the last value within the raw ingested data.

**Note:** Alternatives are: `longLast()`, `doubleLast()`, `floatLast()` and `stringLast()`, which allow you to 
directly specify the output type by using the appropriate method name.  These methods do not have the `$type` parameter.

Example:
```php
$builder->last('email')
```

The `last()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                             |
|----------|-----------------------|------------------|----------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$metric`        | "device"                                     | The metric which you want to compute the last value of.                                                                                     |
| string   | Optional              | `$as`            | "firstDevice"                                | The name which will be used in the output result                                                                                            |
| string   | Optional              | `$type`          | "long"                                       | The output type. This can either be string, long, float or double.                                                                          |
| Closure  | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only compute the last value of the records which match with the given filter. |
 

#### `javascript()`

The `javascript()` aggregation computes an arbitrary JavaScript function over a set of columns (both metrics and dimensions 
are allowed). Your JavaScript functions are expected to return floating-point values.

**NOTE:** JavaScript-based functionality is disabled by default. Please refer to the Druid JavaScript programming guide 
for guidelines about using Druid's JavaScript functionality, including instructions on how to enable it:
https://druid.apache.org/docs/latest/development/javascript.html

Example:

```php
$builder->javascript(
    'result',
    ['x', 'y'],
    "function(current, a, b)      { return current + (Math.log(a) * b); }",
    "function(partialA, partialB) { return partialA + partialB; }",
    "function()                   { return 10; }"
);
```

The `javascript()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                                                                                                      |
|----------|-----------------------|------------------|----------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$as`            | "result"                                     | The name which will be used in the output result                                                                                                                                                                     |
| array    | Required              | `$fieldNames`    | ["metric_field", "dimension_field"]          | The columns which will be given to the fnAggregate function. Both metrics and dimensions are allowed.                                                                                                                |
| string   | Required              | `$fnAggregate`   | See example above.                           | A javascript function which does the aggregation. This function will receive the "current" value as first parameter. The other parameters will be the values of the columns as given in the `$fieldNames` parameter. |
| string   | Required              | `$fnCombine`     | See example above.                           | A function which can combine two aggregation results.                                                                                                                                                                |
| string   | Required              | `$fnReset`       | See example above.                           | A function which will reset a value.                                                                                                                                                                                 |
| Closure  | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only apply the javascript function to the records which match with the given filter.                                                                   |

#### `hyperUnique()`

The `hyperUnique()` aggregation uses HyperLogLog to compute the estimated cardinality of a dimension that has been 
aggregated as a "hyperUnique" metric at indexing time.

Please note: use `distinctCount()` when the Theta Sketch extension is available, as it is much faster. 

See this page for more information:
https://druid.apache.org/docs/latest/querying/hll-old.html#hyperunique-aggregator

Example: 
```php
$builder->hyperUnique('dimension', 'myResult');
```

The `hyperUnique()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**          | **Example** | **Description**                                                                                                                                                                                                      |
|----------|-----------------------|-----------------------|-------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$metric`             | "dimension" |  The dimension that has been aggregated as a "hyperUnique" metric at indexing time.                                                                                                                                  |
| string   | Required              | `$as`                 | "myField"   | The name which will be used in the output result                                                                                                                                                                     |
| bool     | Optional              | `$round`              | true        | TheHyperLogLog algorithm generates decimal estimates with some error. "round" can be set to true to round off estimated values to whole numbers. Note that even with rounding, the cardinality is still an estimate. |
| bool     | Optional              | `$isInputHyperUnique` | false       | Only affects ingestion-time behavior, and is ignored at query-time. Set to true to index pre-computed HLL (Base64 encoded output from druid-hll ise xpected).                                                        | 


#### `cardinality()`

The `cardinality()` aggregation computes the cardinality of a set of Apache Druid (incubating) dimensions, 
using HyperLogLog to estimate the cardinality. 

Please note: use `distinctCount()` when the Theta Sketch extension is available, as it is much faster. 
This aggregator will also be much slower than indexing a column with the `hyperUnique()` aggregator. 

In general, we strongly recommend using the `distinctCount()` or `hyperUnique()` aggregator instead of the `cardinality()` 
aggregator if you do not care about the individual values of a dimension.

When setting `$byRow` to `false` (the default) it computes the cardinality of the set composed of the union of al
dimension values for all the given dimensions. For a single dimension, this is equivalent to:
```sql
SELECT COUNT(DISTINCT(dimension)) FROM <datasource>
```
For multiple dimensions, this is equivalent to something akin to
```sql
SELECT COUNT(DISTINCT(value)) FROM (
SELECT dim_1 as value FROM <datasource>
UNION
SELECT dim_2 as value FROM <datasource>
UNION
SELECT dim_3 as value FROM <datasource>
)
```

When setting `$byRow` to `true` it computes the cardinality by row, i.e. the cardinality of distinct dimension
combinations. This is equivalent to something akin to
```sql
SELECT COUNT(*) FROM ( SELECT DIM1, DIM2, DIM3 FROM <datasource> GROUP BY DIM1, DIM2, DIM3 )
```

For more information, see https://druid.apache.org/docs/latest/querying/hll-old.html#cardinality-aggregator.

Example:

```php 
$builder->cardinality( 'nrOfCategories', ['category_id']);    
```

You can also use a `Closure` function, which will receive a `DimensionBuilder`. In this way you can build more complex
situations, for example:

```php 
$builder->cardinality(
    'nrOfDistinctFirstLetters',
    function(DimensionBuilder $dimensions) {
        // select the first character of all the last names.
        $dimensions->select('last_name', 'lastName', function (ExtractionBuilder $extractionBuilder) {
            $extractionBuilder->substring(1);
        });        
    },
    false, # byRow
    false # round
);
```

The `cardinality()` aggregation method has the following parameters:

| **Type**      | **Optional/Required** | **Argument**                    | **Example**        | **Description**                                                                                                                                                                                                      |
|---------------|-----------------------|---------------------------------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string        | Required              | `$as`                           | "distinctCount"    | The name which will be used in the output result                                                                                                                                                                     |
| Closure/array | Required              | `$dimensionsOrDimensionBuilder` | See example above. | An array with dimension(s) or a function which receives an instance of the DimensionBuilder class. You should select the dimensions which you want to use to calculate the cardinality over.                         |
| bool          | Optional              | `$byRow`                        | false              | See above for more info.                                                                                                                                                                                             |
| bool          | Optional              | `$round`                        | true               | TheHyperLogLog algorithm generates decimal estimates with some error. "round" can be set to true to round off estimated values to whole numbers. Note that even with rounding, the cardinality is still an estimate. |

#### `distinctCount()`

The `distinctCount()` aggregation function computes the distinct number of occurrences of the given dimension.

This method uses the Theta Sketch extension and it should be enabled to make use of this aggregator.  
For more information, see: https://druid.apache.org/docs/latest/development/extensions-core/datasketches-theta.html

Example:
```php
// Count the distinct number of categories. 
$builder->distinctCount('category_id', 'categoryCount');
```

The `distinctCount()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                                                                |
|----------|-----------------------|------------------|----------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimension`     | "category_id"                                | The dimension where you want to count the distinct values from.                                                                                                                |
| string   | Optional              | `$as`            | "categoryCount"                              | The name which will be used in the output result                                                                                                                               |
| int      | Optional              | `$size`          | 16384                                        | Must be a power of 2. Internally, size refers to the maximum number of entries sketch object will retain. Higher size means higher accuracy but more space to store sketches.  |
| Closure  | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only count the records which match with the given filter.                                                        |
  
## Filters

With filters you can filter on certain values. The following filters are available:

#### `where()`

This is probably the most used filter. It is very flexible.

This method uses the following arguments:

| **Type** | **Optional/Required** | **Argument**  | **Example**        | **Description**                                                                                                                                                                         |
|----------|-----------------------|---------------|--------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimension`  | "cityName"         | The dimension which you want to filter.                                                                                                                                                 |
| string   | Required              | `$operator`   | "="                | The operator which you want to use to filter. See below for a complete list of supported operators.                                                                                     |
| mixed    | Required              | `$value`      | "Auburn"           | The value which you want to use in your filter comparison                                                                                                                               |
| Closure  | Optional              | `$extraction` | See example below. | A closure which builds one or more extraction function. These are applied _before_ the filter will be applied. So the filter will use the value returned by the extraction function(s). |
| string   | Optional              | `$boolean`    | "and" / "or"       | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and".                            |

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
    ->where('name', '=', 'jo', function (ExtractionBuilder $extractionBuilder) {
        $extractionBuilder->substring(2);
    });
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

We also support using a `Closure` to group various filters in 1 filter. It will receive a `FilterBuilder`. For example:
```php
$builder->where(function (FilterBuilder $filterBuilder) {
    $filterBuilder->orWhere('namespace', 'Talk');
    $filterBuilder->orWhere('namespace', 'Main');
});
$builder->where('channel', 'en');
```

This would be the same as an SQL equivalent:
```SELECT ... WHERE (namespace = 'Talk' OR 'namespace' = 'Main') AND 'channel' = 'en'; ``` 

As last, you can also supply a raw filter object. For example:
```php
$builder->where( new SelectorFilter('name', 'John') );
```

However, this is not recommended and should not be needed.

#### `orWhere()`

Same as `where()`, but now we will join previous added filters with a `or` instead of an `and`.

#### `whereIn()`

With this method you can filter on records using multiple values. 

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument**  | **Example**        | **Description**                                                                |
|----------|-----------------------|---------------|--------------------|--------------------------------------------------------------------------------|
| string   | Required              | `$dimension`  | country_iso        | The dimension which you want to filter                                         |
| array    | Required              | `$items`      | ["it", "de", "au"] | A list of values. We will return records where the dimension is in this list.  |
| Closure  | Optional              | `$extraction` | See Extractions    | An extraction function to extract a different value from the dimension.        |

#### `whereNotIn()`

This works the same as `whereIn()`, only now we will check if the dimension is NOT in the given values. See `whereIn()` 
for more details.  

#### `whereBetween()`

This filter will select records where the given dimension is greater than or equal to the given `$minValue`, and 
less than the given `$maxValue`.

The SQL equivalent would be:
```SELECT ... WHERE field >= $minValue AND field < $maxValue```

This method has the following arguments:

| **Type**    | **Optional/Required** | **Argument**  | **Example**     | **Description**                                                                                                                                                                                                                                                                      |
|-------------|-----------------------|---------------|-----------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string      | Required              | `$dimension`  | year            | The dimension which you want to filter                                                                                                                                                                                                                                               |
| int/string  | Required              | `$minValue`   | 1990            | The minimum value where the dimension should match. It should be equal or greater than this value.                                                                                                                                                                                   |
| int/string  | Required              | `$maxValue`   | 2000            | The maximum value where the dimension should match. It should be less than this value.                                                                                                                                                                                 |
| Closure     | Optional              | `$extraction` | See Extractions | Extraction function to extract a different value from the dimension.                                                                                                                                                                                                                 |
| string      | Optional              | `$ordering`   | numeric         | Specifies the sorting order to use when comparing values against the dimension. Can be one of the following values: "lexicographic", "alphanumeric", "numeric", "strlen", "version". By default it will be "numeric" if the values are numeric, otherwise it will be "lexicographic" |

#### `whereNotBetween()`

This works the same as `whereBetween()`, only now we will check if the dimension is NOT in between the given values. 
See `whereBetween()` for more details.  

#### `whereInterval()`

The Interval filter enables range filtering on columns that contain long millisecond values, with the boundaries 
specified as ISO 8601 time intervals. It is suitable for the __time column, long metric columns, and dimensions 
with values that can be parsed as long milliseconds.

This filter converts the ISO 8601 intervals to long millisecond start/end ranges.
It will then use a between filter to see if the interval matches. 

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument**  | **Example**       | **Description**                                                      |
|----------|-----------------------|---------------|-------------------|----------------------------------------------------------------------|
| string   | Required              | `$dimension`  | __time            | The dimension which you want to filter                               |
| array    | Required              | `$intervals`  | ['yesterday/now'] | See below for more info                                              |
| Closure  | Optional              | `$extraction` | See Extractions   | Extraction function to extract a different value from the dimension. |


The `$intervals` array can contain the following:
- an `Interval` object
- an raw interval string as used in druid. For example: "2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z"
- an interval string, separating the start and the stop with a / (for example "12-02-2019/13-02-2019") 
- an array which contains 2 elements, a start and stop date. These can be an DateTime object, a unix timestamp or anything which can be parsed by DateTime::__construct

See for more info also the `interval()` method. 

Example:

```php
$builder->whereInterval('__time', ['12-09-2019/13-09-2019', '19-09-2019/20-09-2019']);
```

#### `whereNotInterval()`

This works the same as `whereInterval()`, only now we will check if the dimension is NOT matching the given intervals. 
See `whereInterval()` for more details.  

## Extractions

With an extraction you can _extract_ a value from the dimension. These extractions can be used to select the data (see
`select()`), or to filter on  (see `where()`).

There are several extraction methods available. These are described below. 
See also this page in the druid manual: https://druid.apache.org/docs/latest/querying/dimensionspecs.html#extraction-functions

Please note that it is possible to use multiple extraction functions at the same time. They will be executed in order 
of requested. 

For example, this will extract the first 3 letters of a surname in upper case:

```php
$builder->select('surName', 'nameCategory', function(ExtractionBuilder $extraction) {
    $extraction->substring(3)->upper();
});
```

#### `lookup()`

With this extraction function, you can use a registered lookup function to transform the dimension value to something else.

For example, when you have stored a dimension called `country_id`. However, you want to filter on the country name.
Then you could use a lookup function to transform the `country_id` to a country name, and use that value in your 
where statement. 

Example:
```php
// Match any country like %Nether%
$builder->where('country_id', 'like', '%Nether%', function (ExtractionBuilder $extractionBuilder) {
    // Extract the country name by it's id by usin this lookup function. 
    $extractionBuilder->lookup('country_name_by_id');
});
```

The `lookup()` extraction function has the following arguments:

| **Type**    | **Optional/Required** | **Argument**           | **Example**            | **Description**                                                                                                                                                                                                                                                                                                 |
|-------------|-----------------------|------------------------|------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string      | Required              | `$lookupName`          | "country_name_by_id"   | The name of the registered lookup function to transform the dimension value to another value.                                                                                                                                                                                                                   |
| bool/string | Optional              | `$replaceMissingValue` | `false` or `"Unknown"` | When true, we will keep values which are not known in the lookup function. The original value will be kept. If false, the missing items will not be kept in the result set. If this is a string, we will keep the missing values and replace them with the string value.                                        |
| bool        | Optional              | `$optimize`            | `true`                 | When set to true, we allow the optimization layer (which will run on thebroker) to rewrite the extraction filter if needed.                                                                                                                                                                                     |
| bool/null   | Optional              | `$injective`           | `true`                 | This can override the lookup's own sense of whether or not it is injective. If left unspecified, Druid will use the registered cluster-wide lookup configuration. In general, you should set this property for any lookup that is naturally one-to-one, to allow Druid to run your queries as fast as possible. |

#### `inlineLookup()`

With the `inlineLookup()` extraction function, you can transform the dimension's value using a given list, instead of
using a registered lookup function (as `lookup()` does).

Example:

```php
$builder->select('likesAnimals', 'LikesAnimals', function(ExtractionBuilder $extraction) {
    $extraction->inlineLookup(['y' => 'Yes', 'n' => 'No']);
});
```

The `inlineLookup()` extraction function has the following arguments:

| **Type**    | **Optional/Required** | **Argument**           | **Example**                 | **Description**                                                                                                                                                                                                                                                                                                                                                                                         |
|-------------|-----------------------|------------------------|-----------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| array       | Required              | `$map`                 | ["y" => "Yes", "n" => "No"] | An array with key => value items which will be used as lookup map.                                                                                                                                                                                                                                                                                                                                      |
| bool/string | Optional              | `$replaceMissingValue` | `false` or `"Unknown"`      | When true, we will keep values which are not known in the lookup function. The original value will be kept. If false, the missing items will not be kept in the result set. If this is a string, we will keep the missing values and replace them with the string value.                                                                                                                                |
| bool        | Optional              | `$optimize`            | `true`                      | When set to true, we allow the optimization layer (which will run on thebroker) to rewrite the extraction filter if needed.                                                                                                                                                                                                                                                                             |
| bool/null   | Optional              | `$injective`           | `true`                      | Whether or not this list is injective. Injective lookups should include all possible keys that may show up in your dataset, and should also map all keys to unique values. This matters because non-injective lookups may map different keys to the same value, which must be accounted for during aggregation, lest query results contain two result values that should have been aggregated into one. |

#### `format()`

With the extraction function `format()` you can format a dimension value according to the given format string.
The formatting is equal to the format used in the PHP's sprintf method. 

Example:
```php
// Display the number with leading zero's
$builder->select('number', 'myBigNumber', function(ExtractionBuilder $extraction) {
  $extraction->format('%03d');
});
```

The `format()` extraction function has the following arguments:

| **Type** | **Optional/Required** | **Argument**         | **Example**   | **Description**                                                                                                                                  |
|----------|-----------------------|----------------------|---------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$sprintfExpression` | "%02d"        | The format string which will be used to format the dimensions value.                                                                             |
| string   | Optional              | `$nullHandling`      | "emptyString" | Can be one of nullString, emptyString or returnNull. With "[%s]" format, each configuration will result [null], [], null. Default is nullString. |

#### `upper()`

The `upper()` extraction function will change the given dimension value to upper case. Optionally user can specify the 
language to use in order to perform upper transformation.

Example:
```php
// Return the city name in upper case.
$builder->select('cityName', 'city', function(ExtractionBuilder $extraction) {
  $extraction->upper();
});
```

The `upper()` extraction function has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example** | **Description**                                              |
|----------|-----------------------|--------------|-------------|--------------------------------------------------------------|
| string   | Optional              | `$locale`    | "fr"        | The language to use in order to perform upper transformation |

#### `lower()`

The `lower()` extraction function will change the given dimension value to lower case. Optionally user can specify the 
language to use in order to perform lower transformation.

Example:
```php
// compare the city name in lower case
$builder->where('cityName', '=', strtolower($city), function(ExtractionBuilder $extraction) {
  $extraction->lower();
});
```

The `lower()` extraction function has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example** | **Description**                                              |
|----------|-----------------------|--------------|-------------|--------------------------------------------------------------|
| string   | Optional              | `$locale`    | "fr"        | The language to use in order to perform lower transformation |

#### `timeParse()`

The `timeParse()` extraction function parses dimension values as timestamps using the given input format, 
and returns them formatted using the given output format. 

The date format can be given in the Joda DateTimeFormat or in SimpleDateFormat. 

If `$jodaFormat` is true, time formats are described in the Joda DateTimeFormat documentation. If `$jodaFormat` is
false (or unspecified) then formats are described in the SimpleDateFormat documentation. In general, we
recommend setting `$jodaFormat` to true since Joda format strings are more common in Druid APIs and since Joda 
handles certain edge cases (like weeks and week-years near the start and end of calendar years) 
in a more ISO8601 compliant way.

See: 
  * Joda DateTimeFormat: http://www.joda.org/joda-time/apidocs/org/joda/time/format/DateTimeFormat.html
  * SimpleDateFormat: http://icu-project.org/apiref/icu4j/com/ibm/icu/text/SimpleDateFormat.html

**Note**: if you are working with the `__time` dimension, you should consider using the `timeFormat()` extraction function instead 
instead, which works on time value directly as opposed to string values.

If a value cannot be parsed using the provided timeFormat, it will be returned as-is.

Example:

```php
// Change the date format from something like "1984-05-23" to "23 May 1984"
$builder->select('birthday', 'dayOfBirth', function(ExtractionBuilder $extraction) {
    $extraction->timeParse('yyyy-MM-dd', 'dd MMMM yy');
});
```

The `timeParse()` extraction function has the following arguments:

| **Type** | **Optional/Required** | **Argument**    | **Example** | **Description**                                                         |
|----------|-----------------------|-----------------|-------------|-------------------------------------------------------------------------|
| string   | Required              | `$inputFormat`  | yyyy-MM-dd  | The format which is used to parse the dimensions value.                 |
| string   | Required              | `$outputFormat` | dd MMMM yy  | The format which is used to display the parsed value.                   |
| bool     | Optional              | `$jodaFormat`   | true        | Whether or not to use the Joda DateTimeFornat. See for more info above. |


#### `timeFormat()`

The `timeFormat()` extraction function will format the dimension according to the given format string, time zone, and locale.
The format should be given in Joda DateTimeFormat. 

See: http://www.joda.org/joda-time/apidocs/org/joda/time/format/DateTimeFormat.html

For `__time` dimension values, this formats the time value bucketed by the aggregation granularity.

For a regular dimension, it assumes the string is formatted in ISO-8601 date and time format.

Example:
```php
// Format the time like "23 May 2019"
$builder->select('__time', 'time', function(ExtractionBuilder $extraction) {
    $extraction->timeFormat('dd MMMM yyyy', Granularity::DAY);
});
```

The `timeFormat()` extraction function has the following arguments:

| **Type**    | **Optional/Required** | **Argument**      | **Example**   | **Description**                                                                                                                                                                      |
|-------------|-----------------------|-------------------|---------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string/null | Optional              | `$format`         | dd-MM-yyyy    | Date time format for the resulting dimension value, in Joda TimeDateTimeFormat, or null to use the default ISO8601 format.                                                           |
| string/null | Optional              | `$granularity`    | day           | Granularity to apply before formatting, or omit to not apply any granularity.                                                                                                        |
| string/null | Optional              | `$locale`         | en-GB         | Locale (language and country) to use, given as a IETF BCP 47 language tag, e.g. en-US, en-GB, fr-FR, fr-CA, etc.                                                                     |
| string/null | Optional              | `$timeZone`       | Europe/Berlin | time zone to use in IANA tz database format, e.g. Europe/Berlin (this can possibly be different than the aggregation time-zone)                                                      |
| bool/null   | Optional              | `$asMilliseconds` | `true`        | Set to true to treat input strings as milliseconds rather thanISO8601 strings. Additionally, if format is null or not specified, output will be in milliseconds rather than ISO8601. |

#### `regex()`

The `regex()` extraction function will return the first matching group for the given regular expression. 
If there is no match, it returns the dimension value as is.

Example:

```php
// Zipcodes
$builder->select('day', 'day', function(ExtractionBuilder $extraction) {
    // Transform 'Monday', 'Tuesday', 'Wednesday' into 'Mon', 'Tue', 'Wed'.
    $extraction->regex('(\\w\\w\\w).*');
});
```

The `regex()` extraction function has the following arguments:

| **Type**    | **Optional/Required** | **Argument**           | **Example** | **Description**                                                                                                                                                                                                                                          |
|-------------|-----------------------|------------------------|-------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string      | Required              | `$regularExpression`   | `[0-9]*`    | The regular expression where the dimensions value should match with.                                                                                                                                                                                     |
| int         | Optional              | `$groupToExtract`      | 1           | If "$groupToExtract" is set, it will control which group from the match to extract. Index zero extracts the string matching the entire pattern.                                                                                                          |
| bool/string | Optional              | `$replaceMissingValue` | "Unknown"   | When true, we will keep values which are not matched by the regexp. The value will be null. If false, the missing items will not be kept in the result set. If this is a string, we will keep the missing values and replace them with the string value. |


#### `partial()`

The `partial()` extraction function will return the dimension value unchanged if the regular expression matches, otherwise returns null.

See this page for more information about the regular expression format: http://docs.oracle.com/javase/6/docs/api/java/util/regex/Pattern.html

Example:

```php
// Zipcodes
$builder->select('zipcode', 'zipcode', function(ExtractionBuilder $extraction) {
    // filter out all incorrect zipcodes, only allow zipcodes in format "2881 AB"
    $extraction->partial('[0-9]{4} [A-Z]{2}');
});
```

The `partial()` extraction function has the following arguments:

| **Type** | **Optional/Required** | **Argument**         | **Example** | **Description**                                                                                                     |
|----------|-----------------------|----------------------|-------------|---------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$regularExpression` | `[0-9]*`    | The regular expression where the dimensions value should match with. All none matching values are changed to `null` |


#### `searchQuery()`

The `searchQuery()` extraction function will return the values which will match the given 
search string(s), or `null` when there is no match. 

Example:

```php
// Zipcodes
$builder->select('page', 'page', function(ExtractionBuilder $extraction) {
    // Filter out all pages containing the word "talk" (case insensitive)
    $extraction->searchQuery('talk', false);
});
``` 

The `searchQuery()` extraction function has the following arguments:

| **Type**     | **Optional/Required** | **Argument**     | **Example** | **Description**                                                                                                                                                                                            |
|--------------|-----------------------|------------------|-------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string/array | Required              | `$valueOrValues` | "Talk"      | The word (string) or words (array) where the dimension should match with. If this word is in the dimension, it matches. When multiple words are given, all of then should match with the dimensions value. |
| bool         | Optional              | `$caseSensitive` | true        | If "$groupToExtract" is set, it will control which group from the match to extract. Index zero extracts the string matching the entire pattern.                                                            |


#### `substring()`

The `substring()` extraction function will return a substring of the dimension value starting from the supplied index 
and of the desired length. Both index and length are measured in the number of Unicode code units present in the string 
as if it were encoded in UTF-16. Note that some Unicode characters may be represented by two code units. 
This is the same behavior as the Java String class's "substring" method.

If the desired length exceeds the length of the dimension value, the remainder of the string starting at 
index will be returned. If index is greater than the length of the dimension value, null will be returned.

Example:

```php
// Filter on all surname's starting with the letter B
$builder->where('surname', '=', 'B', function(ExtractionBuilder $extraction) {
    $extraction->substring(0, 1);
});
```

The `substring()` extraction function has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example** | **Description**                                                             |
|----------|-----------------------|--------------|-------------|-----------------------------------------------------------------------------|
| int      | Required              | `$index`     | 2           | The starting index from where the dimension's value should be returned.     |
| int      | Optional              | `$length`    | 5           | The number of characters which should be returned from the $index position. |

#### `javascript()`

The `javascript()` extraction function will return the dimension value, as transformed by the given JavaScript function.

For regular dimensions, the input value is passed as a string.

For the `__time` dimension, the input value is passed as a number representing the number of milliseconds since January 1, 1970 UTC.

Example:
```php
$builder->select('__time', 'second', function(ExtractionBuilder $extraction) {
    $extraction->javascript("function(t) { return 'Second ' + Math.floor((t % 60000) / 1000); }");
});
```

**Advanced example:**

The javascript function is a good alternative to do bitwise operator expressions, as they are currently not yet 
supported by druid. A feature request has been opened, see: https://github.com/apache/incubator-druid/issues/8560

Until then, you can use something like this to extract a value using a "bitwise and":
```php
int $binaryFlagToMatch = 16;

// Select the fields where the 5th bit is enabled
$builder->where('flags', '=', $binaryFlagToMatch, function(ExtractionBuilder $extraction) use( $binaryFlagToMatch ) {   
    // Do a binary "AND" flag comparison on a 64 bit int. The result will either be the 
    // $binaryFlagToMatch, or 0 when it's bit is not set. 
    $extraction->javascript('
        function(v1) { 
            var v2 = '.$binaryFlagToMatch.'; 
            var hi = 0x80000000; 
            var low = 0x7fffffff; 
            var hi1 = ~~(v1 / hi); 
            var hi2 = ~~(v2 / hi); 
            var low1 = v1 & low; 
            var low2 = v2 & low; 
            var h = hi1 & hi2; 
            var l = low1 & low2; 
            return (h*hi + l); 
        }
    ');
});
``` 

**NOTE:** JavaScript-based functionality is disabled by default. Please refer to the Druid JavaScript programming guide 
for guidelines about using Druid's JavaScript functionality, including instructions on how to enable it:
https://druid.apache.org/docs/latest/development/javascript.html

The `javascript()` extraction function has the following arguments:

| **Type** | **Optional/Required** | **Argument**  | **Example**        | **Description**                                                                                          |
|----------|-----------------------|---------------|--------------------|----------------------------------------------------------------------------------------------------------|
| string   | Required              | `$javascript` | See examples above | The javascript function which transforms the given dimension value.                                      |
| boolean  | Optional              | `$injective`  | true               | Set to true if this function preserves the uniqueness of the dimensions value. Default value is `false`. |

#### `bucket()`

The `bucket()` extraction function is used to bucket numerical values in each range of the given size by converting 
them to the same base value. Non numeric values are converted to null.

Example:
```php
// Group all ages into "groups" by 10, 20, 30, etc. 
$builder->select('age', 'ageGroup', function(ExtractionBuilder $extraction) {
    $extraction->bucket(10);
}); 
```

The `bucket()` extraction function has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example** | **Description**                                                  |
|----------|-----------------------|--------------|-------------|------------------------------------------------------------------|
| int      | Optional              | `$size`      | 10          | The size of the bucket where the numerical values are grouped in |
| int      | Optional              | `$offset`    | 2           | The offset for the buckets                                       |


## Having

With having filters, you can filter out records _after_ the data has been retrieved. This allows you to filter on aggregated values.

See also this page: https://druid.apache.org/docs/latest/querying/having.html

Below are all the having methods explained.

#### `having()`

The `having()` filter is very simular to the `where()` filter. It is very flexible.

This method has the following arguments:

| **Type**   | **Optional/Required** | **Argument**   | **Example**        | **Description**                                                                                                                                                            |
|------------|-----------------------|----------------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string     | Required              | `$having`      | "totalClicks"      | The metric which you want to filter.                                                                                                                                       |
| string     | Required              | `$operator`    | ">"                | The operator which you want to use to filter. See below for a complete list of supported operators.                                                                        |
| string/int | Required              | `$value`       | 50                 | The value which you want to use in your filter comparison                                                                                                                  |
| string     | Optional              | `$boolean`     | "and" / "or"       | This influences how this having-filter will be joined with previous added having-filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

The following `$operator` values are supported:

| **Operator**   | **Description**                                                                                                                                                 |
|----------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------|
| =              | Check if the metric is equal to the given value.                                                                                                                |
| !=             | Check if the metric is not equal to the given value.                                                                                                            |
| <>             | Same as `!=`                                                                                                                                                    |
| >              | Check if the metric is greater than the given value.                                                                                                            |
| >=             | Check if the metric is greater than or equal to the given value.                                                                                                |
| <              | Check if the metric is less than the given value.                                                                                                               |
| <=             | Check if the metric is less than or equal to the given value.                                                                                                   |
| like           | Check if the metric matches a SQL LIKE expression. Special characters supported are "%" (matches any number of characters) and "_" (matches any one character). |
| not like       | Same as `like`, only now the metric should not match.                                                                                                           |

This method supports a quick equals shorthand. Example:
```php
// select everybody with 2 kids
$builder->having('sumKids', 2);
```

Is the same as
```php
$builder->having('sumKids', '=', 2);
```

We also support using a `Closure` to group various havings in 1 filter. It will receive a `HavingBuilder`. For example:
```php
$builder->having(function (FilterBuilder $filterBuilder) {
    $filterBuilder->orHaving('sumKats', '>', 0);
    $filterBuilder->orHaving('sumDogs', '>', 0);
});
$builder->having('sumKids', '=', 0);
```

This would be the same as an SQL equivalent:
```SELECT ... HAVING (sumKats > 0 OR sumDogs > 0) AND sumKids = 0;``` 

As last, you can also supply a raw filter or having-filter object. For example:
```php
// exampe using a having filter
$builder->having( new GreaterThanHavingFilter('totalViews', 15) );

// example using a "normal" filter.
$builder->having( new SelectorFilter('totalViews', '15') );
```

However, this is not recommended and should not be needed.

#### `orHaving()`

Same as `having()`, but now we will join previous added having-filters with a `or` instead of an `and`.

## Virtual Columns

Virtual columns allow you to create a new "virtual" column based on an expression. This is very powerful, but not well
documented in the Druid Manual. 

Druid expressions allow you to do various actions, like:

 * Execute a lookup and use the result
 * Execute mathematical operations on values  
 * Use if, else expressions
 * Concat strings
 * Use a "case" statement
 * Etc.
 
For the full list of available expressions, see this page: https://druid.apache.org/docs/latest/misc/math-expr.html

To use a virtual column, you should use the `virtualColumn()` method:

#### `virtualColumn()`

This method creates a virtual column based on the given expression. 

Virtual columns are queryable column "views" created from a set of columns during a query.

A virtual column can potentially draw from multiple underlying columns, although a virtual column always
presents itself as a single column.

Virtual columns can be used as dimensions or as inputs to aggregators.

**NOTE**: virtual columns are NOT automatically added to your output. You should select it separately if you want to
add it also to your output. Use `selectVirtual()` to do both at once.

Example:

```php
// Increase our reward with $2,00 if this sale was done by a promoter. 
$builder->virtualColumn('if(promo_id > 0, reward + 2, 0)', 'rewardWithPromoterPayout', 'double')
    // Now sum all our rewards with the promoter payouts included.
    ->doubleSum('rewardWithPromoterPayout', 'totalRewardWithPromoterPayout');
```

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument**  | **Example**               | **Description**                                                                                                          |
|----------|-----------------------|---------------|---------------------------|--------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$expression` | if( dimension > 0, 2, 1)  | The expression which you want to use to create this virtual column.                                                      |
| string   | Required              | `$as`         | "myVirtualColumn"         | The name of the virtual column created. You can use this name in a dimension (select it) or in an aggregation function.  |
| string   | Optional              | `$type`       | "string"                  | The output type of this virtual column. Possible values are: string, float, long and double. Default is string.          |
 
#### `selectVirtual()`

This method creates a virtual column as the method `virtualColumn()` does, but this method also selects the virtual
column in the output. 

Example:
```php
// Select the mobile device type as text, but only if isMobileDevice = 1 
$builder->selectVirtual(
    "if( isMobileDevice = 1, case_simple( mobileDeviceType, '1', 'samsung', '2', 'apple', '3', 'nokia', 'other'), 'no mobile device')", 
    "deviceType"
);
```  

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument**  | **Example**               | **Description**                                                                                                          |
|----------|-----------------------|---------------|---------------------------|--------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$expression` | if( dimension > 0, 2, 1)  | The expression which you want to use to create this virtual column.                                                      |
| string   | Required              | `$as`         | "myVirtualColumn"         | The name of the virtual column created. You can use this name in a dimension (select it) or in an aggregation function.  |
| string   | Optional              | `$type`       | "string"                  | The output type of this virtual column. Possible values are: string, float, long and double. Default is string.          |

## Post Aggregations

#### `fieldAccess()`

The `fieldAccess()` post aggregator method is not really a aggregation method itself, but you need it to access fields which are used 
in the other post aggregations. 

For example, when you want to calculate the average salary per job function:
```php
$builder
    ->select('jobFunction')
    ->doubleSum('salary', 'totalSalary')
    ->longSum('nrOfEmployees')
    // avgSalary = totalSalary / nrOfEmployees   
    ->divide('avgSalary', function(PostAggregationsBuilder $builder) {
        $builder->fieldAccess('totalSalary');
        $builder->fieldAccess('nrOfEmployees');
    });
```

However, we you can also use this shorthand, which will be converted to `fieldAccess` methods:

```php
$builder
    ->select('jobFunction')
    ->doubleSum('salary', 'totalSalary')
    ->longSum('nrOfEmployees')
    // avgSalary = totalSalary / nrOfEmployees   
    ->divide('avgSalary', ['totalSalary', 'nrOfEmployees']);
```

This is exactly the same. We will convert the given fields to `fieldAccess()` for you.

The `fieldAccess()` post aggregator has the following arguments:

| **Type** | **Optional/Required** | **Argument**            | **Example**  | **Description**                                                                                 |
|----------|-----------------------|-------------------------|--------------|-------------------------------------------------------------------------------------------------|
| string   | Required              | `$aggregatorOutputName` | totalRevenue | This refers to the output name of the aggregator given in the aggregations portion of the query |
| string   | Required              | `$as`                   | myField      | The output name as how we can access it                                                         |
| string   | Optional              | `$finalizing`           | false        | Set this to true if you want to return a finalized value, such as an estimated cardinality      |

#### `constant()`

The `constant()` post aggregator method allows you to define a constant which can be used in a post aggregation function. 

For example, when you want to calculate the area of a circle based on the radius, you can use a formula like below:

Find the circle area based on the formula radius x radius x pi. 
```php
$builder
    ->select('radius')
    ->multiply('area', function(PostAggregationsBuilder $builder){
        $builder->multipli('r2', ['radius', 'radius']);
        $builder->constant('3.141592654', 'pi');
    });
```

The `constant()` post aggregator has the following arguments:

| **Type**  | **Optional/Required** | **Argument**    | **Example** | **Description**                          |
|-----------|-----------------------|-----------------|-------------|------------------------------------------|
| int/float | Required              | `$numericValue` | 3.14        | This will be our static value            |
| string    | Required              | `$as`           | pi          | The output name as how we can access it  |

#### `divide()`

The `divide()` post aggregator method divides the given fields. If a value is divided by 0, the result will always be 0.

Example:
```php
$builder
    ->select('jobFunction')
    ->doubleSum('salary', 'totalSalary')
    ->longSum('nrOfEmployees')
    // avgSalary = totalSalary / nrOfEmployees   
    ->divide('avgSalary', ['totalSalary', 'nrOfEmployees']);
```

The first parameter is the name as the result will be available in the output. The fields which you want to divide can 
be supplied in various ways. These ways are described below:

**Method 1: array**

You can supply the fields which you want to use in your division as an array. They will be converted to `fieldAccess()` 
calls for you. 

Example:
```php
$builder->divide('avgSalary', ['totalSalary', 'nrOfEmployees']);
```

**Method 2: Variable-length argument lists**

You can supply the fields which you want to use in your division as extra arguments in the method call. 
They will be converted to `fieldAccess()` calls for you.

Example:
```php
// This will become: avgSalary = totalSalary / nrOfEmployees / totalBonus
$builder->divide('avgSalary', 'totalSalary', 'nrOfEmployees', 'totalBonus');
``` 

**Method 3: Closure**

You can also supply a closure, which allows you to build more advance math calculations.

Example:
```php
// This will become: avgSalary = totalSalary / nrOfEmployees / ( bonus + tips )
$builder->divide('avgSalary', function(PostAggregationsBuilder $builder){    
    $builder->fieldAccess('totalSalary');
    $builder->fieldAccess('nrOfEmployees');    

    $builder->add('totalBonus', ['bonus', 'tips']);    
});
```


The `divide()` post aggregator has the following arguments:

| **Type**                | **Optional/Required** | **Argument**      | **Example**                      | **Description**                                                      |
|-------------------------|-----------------------|-------------------|----------------------------------|----------------------------------------------------------------------|
| string                  | Required              | `$as`             | pi                               | The output name as how we can access it                              |
| array/Closure/...string | Required              | `$fieldOrClosure` | ['totalSalary', 'nrOfEmployees'] | The fields which you want to divide. See above for more information. |


#### `multiply()`

The `multiply()` post aggregator method multiply the given fields. 

Example:
```php
$builder->multiply('volume', ['width', 'height', 'depth']);
```

The `multiply()` post aggregator has the following arguments:

| **Type**                | **Optional/Required** | **Argument**      | **Example**                      | **Description**                                                                 |
|-------------------------|-----------------------|-------------------|----------------------------------|---------------------------------------------------------------------------------|
| string                  | Required              | `$as`             | pi                               | The output name as how we can access it                                         |
| array/Closure/...string | Required              | `$fieldOrClosure` | ['totalSalary', 'nrOfEmployees'] | The fields which you want to multiply. See the `divide()` method for more info. |


#### `subtract()`

The `subtract()` post aggregator method subtract the given fields. 

Example:
```php
$builder->subtract('total', ['revenue', 'taxes']);
```

The `subtract()` post aggregator has the following arguments:

| **Type**                | **Optional/Required** | **Argument**      | **Example**                      | **Description**                                                                 |
|-------------------------|-----------------------|-------------------|----------------------------------|---------------------------------------------------------------------------------|
| string                  | Required              | `$as`             | pi                               | The output name as how we can access it                                         |
| array/Closure/...string | Required              | `$fieldOrClosure` | ['totalSalary', 'nrOfEmployees'] | The fields which you want to subtract. See the `divide()` method for more info. |


#### `add()`

The `add()` post aggregator method add the given fields. 

Example:
```php
$builder->add('total', ['salary', 'bonus']);
```

The `add()` post aggregator has the following arguments:

| **Type**                | **Optional/Required** | **Argument**      | **Example**                      | **Description**                                                            |
|-------------------------|-----------------------|-------------------|----------------------------------|----------------------------------------------------------------------------|
| string                  | Required              | `$as`             | pi                               | The output name as how we can access it                                    |
| array/Closure/...string | Required              | `$fieldOrClosure` | ['totalSalary', 'nrOfEmployees'] | The fields which you want to add. See the `divide()` method for more info. |


#### `quotient()`

The `quotient()` post aggregator method will calculate the quotient over the given field values. The quotient division 
behaves like regular floating point division. 

Example:
```php
// for example: quotient = 15 / 4 = 3 (e.g., how much times fits 4 into 15?)
$builder->quotient('quotient', ['dividend', 'divisor']);
```

The `add()` post aggregator has the following arguments:

| **Type**                | **Optional/Required** | **Argument**      | **Example**                      | **Description**                                                                 |
|-------------------------|-----------------------|-------------------|----------------------------------|---------------------------------------------------------------------------------|
| string                  | Required              | `$as`             | pi                               | The output name as how we can access it                                         |
| array/Closure/...string | Required              | `$fieldOrClosure` | ['totalSalary', 'nrOfEmployees'] | The fields which you want to quotient. See the `divide()` method for more info. |


#### `longGreatest()` and `doubleGreatest()`

The `longGreatest()` and `doubleGreatest()` post aggregation methods computes the maximum of all fields. 

The difference between the `doubleMax()` aggregator and the `doubleGreatest()` post-aggregator is that doubleMax returns 
the highest value of all rows for one specific column while doubleGreatest returns the highest value of multiple columns 
in one row. These are similar to the SQL MAX and GREATEST functions.

Example:

```php
$builder 
  ->longSum('a', 'totalA')
  ->longSum('b', 'totalB')
  ->longSum('c', 'totalC')
  ->longGreatest('highestABC', ['a', 'b', 'c']);    
```

The `longGreatest()` and `doubleGreatest()` post aggregator have the following arguments:

| **Type**      | **Optional/Required** | **Argument**      | **Example**        | **Description**                                                                                                                          |
|---------------|-----------------------|-------------------|--------------------|------------------------------------------------------------------------------------------------------------------------------------------|
| string        | Required              | `$as`             | "highestValue"     | The name which will be used in the output result                                                                                         |
| Closure/array | Required              | `$fieldOrClosure` | See example above. | The fields where you want to select the greatest value over. This can be done in multiple ways. See the `divide()` method for more info. |

#### `longLeast()` and `doubleLeast()`

The `longLeast()` and `doubleLeast()` post aggregation methods computes the maximum of all fields. 

The difference between the `doubleMin()` aggregator and the `doubleLeast()` post-aggregator is that doubleMin returns 
the lowest value of all rows for one specific column while doubleLeast returns the lowest value of multiple columns 
in one row. These are similar to the SQL MIN and LEAST functions.

Example:

```php
$builder 
  ->longSum('a', 'totalA')
  ->longSum('b', 'totalB')
  ->longSum('c', 'totalC')
  ->longLeast('lowestABC', ['a', 'b', 'c']);    
```

The `longLeast()` and `doubleLeast()` post aggregator have the following arguments:

| **Type**      | **Optional/Required** | **Argument**      | **Example**        | **Description**                                                                                                                        |
|---------------|-----------------------|-------------------|--------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| string        | Required              | `$as`             | "lowestValue"      | The name which will be used in the output result                                                                                       |
| Closure/array | Required              | `$fieldOrClosure` | See example above. | The fields where you want to select the lowest value over. This can be done in multiple ways. See the `divide()` method for more info. |

#### `postJavascript()`

The `postJavascript()` post aggregation method allows you to apply the given javascript function over the given fields.
Fields are passed as arguments to the JavaScript function in the given order.

**NOTE:** JavaScript-based functionality is disabled by default. Please refer to the Druid JavaScript programming guide 
for guidelines about using Druid's JavaScript functionality, including instructions on how to enable it:
https://druid.apache.org/docs/latest/development/javascript.html

Example:
```php
$builder->postJavascript(
    'absPercent',
    'function(delta, total) { return 100 * Math.abs(delta) / total; }',
    ['delta', 'total']
);    
```

The `postJavascript()` post aggregation method has the following arguments:

| **Type**      | **Optional/Required** | **Argument**      | **Example**        | **Description**                                                                                                                                        |
|---------------|-----------------------|-------------------|--------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------|
| string        | Required              | `$as`             | "highestValue"     | The name which will be used in the output result                                                                                                       |
| string        | Required              | `$function`       | See example above. | A string containing the javascript function which will be applied to the given fields.                                                                 |
| Closure/array | Required              | `$fieldOrClosure` | See example above. | The fields where you want to apply the given javascript function over. This can be supplied in multiple ways. See the `divide()` method for more info. |

#### `hyperUniqueCardinality()`

The `hyperUniqueCardinality()` post aggregator is used to wrap a hyperUnique object such that it can be used in post aggregations.

Example:
```php
$builder
  ->count('rows')
  ->hyperUnique('unique_users', 'uniques')
  ->divide('averageUsersPerRow', function(PostAggregationsBuilder $builder){    
      $builder->hyperUniqueCardinality('unique_users');
      $builder->fieldAccess('rows');    
  });
```

The `hyperUniqueCardinality()` post aggregator has the following arguments:

| **Type** | **Optional/Required** | **Argument**        | **Example** | **Description**                                                                     |
|----------|-----------------------|---------------------|-------------|-------------------------------------------------------------------------------------|
| string   | Required              | `$hyperUniqueField` | myField     | The name of the hyperUnique field where you want to retrieve the cardinality from.  |
| string   | Optional              | `$as`               | myResult    | The name which will be used in the output result.                                   |

## `DruidClient::metadata()`

Besides querying data, the `DruidClient` class also allows you to extract metadata from your druid setup.
 
The `metadata()` method returns a `MetadataBuilder` instance. With this instance you can retrieve various metadata
information about your druid setup. 

Below we have described the most common used methods.

#### `metadata()->intervals()`

This method returns all intervals for the given `$dataSource`. 

Example:
```php
$intervals = $client->metadata()->intervals('wikipedia');
```

The `intervals()` method has 1 parameters: 

| **Type** | **Optional/Required** | **Argument**   | **Example** | **Description**                                                                                                                                                                                                                                                                                                                                                      |
|----------|-----------------------|----------------|-------------|-----------------------------------------------------------------------------------|
| string   | Required              | `$dataSource`  | "wikipedia" | The name of the dataSource (table) which you want to retrieve the intervals from. |


It will return the response like this:
```
[
  "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => [ "size" => 75208,  "count" => 4 ],
  "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => [ "size" => 161870, "count" => 8 ],
]
```

#### `metadata()->interval()`

The `interval()` method on the MetadataBuilder will return all details regarding the given interval.

Example:
```php
// retrieve the details regarding the given interval.
$response = $client->metadata()->interval('wikipedia', '2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z');
```

The `interval()` method has the following parameters:

| **Type** | **Optional/Required** | **Argument**  | **Example**                                         | **Description**                                                                          |
|----------|-----------------------|---------------|-----------------------------------------------------|------------------------------------------------------------------------------------------|
| string   | Required              | `$dataSource` | "wikipedia"                                         | The name of the dataSource (table) which you want to retrieve interval information from. |
| string   | Required              | `$interval`   | "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" | The "raw" interval where you want to retrieve details for.                               |

It will return an array as below:
```
$response = [
    '2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z' =>
        [
            'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-26T18:30:14.418Z' =>
                [
                    'metadata' =>
                        [
                            'dataSource'    => 'wikipedia',
                            'interval'      => '2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z',
                            'version'       => '2019-09-26T18:30:14.418Z',
                            'loadSpec'      =>
                                [
                                    'type' => 'local',
                                    'path' => '/etc/apache-druid-0.15.1-incubating/var/druid/segments/wikipedia/2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z/2019-09-26T18:30:14.418Z/0/index.zip',
                                ],
                            'dimensions'    => 'added,channel,cityName,comment,countryIsoCode,countryName,deleted,delta,isAnonymous,isMinor,isNew,isRobot,isUnpatrolled,metroCode,namespace,page,regionIsoCode,regionName,user',
                            'metrics'       => '',
                            'shardSpec'     =>
                                [
                                    'type'         => 'numbered',
                                    'partitionNum' => 0,
                                    'partitions'   => 0,
                                ],
                            'binaryVersion' => 9,
                            'size'          => 4817636,
                            'identifier'    => 'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-26T18:30:14.418Z',
                        ],
                    'servers'  =>
                        [
                            0 => 'localhost:8083',
                        ],
                ],
        ],
];
```

#### `metadata()->structure()`

The `structure()` method creates a `Structure` object which represents the structure for the given dataSource.
It will retrieve the structure for the last known interval, or for the interval which you supply.

Example:
```php
// Retrieve the strucutre of our dataSource
$structure = $client->metadata()->structure('wikipedia');
``` 

The `structure()` method has the following parameters:

| **Type** | **Optional/Required** | **Argument**  | **Example** | **Description**                                                                                                                                                   |
|----------|-----------------------|---------------|-------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dataSource` | "wikipedia" | The name of the dataSource (table) which you want to retrieve interval information from.                                                                          |
| string   | Optional              | `$structure`  | "last"      | The interval where we read the structure data from. You can use "first", "last" or a raw interval string like "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" |

Example response:
```
Level23\Druid\Metadata\Structure Object
(
    [dataSource] => wikipedia
    [dimensions] => Array
        (
            [channel] => STRING
            [cityName] => STRING
            [comment] => STRING
            [countryIsoCode] => STRING
            [countryName] => STRING                        
            [isAnonymous] => STRING
            [isMinor] => STRING
            [isNew] => STRING
            [isRobot] => STRING
            [isUnpatrolled] => STRING            
            [namespace] => STRING
            [page] => STRING
            [regionIsoCode] => STRING
            [regionName] => STRING
            [user] => STRING
        )

    [metrics] => Array
        (
            [added] => LONG
            [deleted] => LONG
            [delta] => LONG
            [metroCode] => LONG 
        )
)
``` 

## MISC

More info to come!

For testing/building, run:
```
infection --threads=4

ant phpstan
```
