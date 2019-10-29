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
 - Support for building metricSpec and DimensionSpec in CompactTaskBuilder 
 - Implement support for Spatial filters
 - Implement support for multi-value dimensions
 - Update documentation for reindex / compact tasks 

## Examples

There are several examples which are written on the single-server tutorial of druid. 
See [this](examples/README.md) page for more information.

# Table of Contents

  - [DruidClient](#druidclient)
    - [DruidClient::query()](#druidclientquery)
    - [DruidClient::compact()](#druidclientcompact)
    - [DruidClient::reindex()](#druidclientreindex)
    - [DruidClient::taskStatus()](#druidclienttaskstatus)
    - [DruidClient::metadata()](#druidclientmetadata)    
  - [QueryBuilder: Generic Query Methods](#querybuilder-generic-query-methods)
    - [interval()](#interval)
    - [orderBy()](#orderby)
    - [orderByDirection()](#orderbydirection)
    - [pagingIdentifier()](#pagingidentifier)
    - [subtotals()](#subtotals)
    - [metrics()](#metrics)
    - [dimensions()](#dimensions)
    - [toArray()](#toarray)
    - [toJson()](#tojson)
  - [QueryBuilder: Dimension Selections](#querybuilder-dimension-selections)
    - [select()](#select)
    - [lookup()](#lookup)
  - [QueryBuilder: Metric Aggregations](#querybuilder-metric-aggregations)
    - [count()](#count)
    - [sum()](#sum)
    - [min()](#min)
    - [max()](#max)
    - [first()](#first)
    - [last()](#last)
    - [javascript()](#javascript)
    - [hyperUnique()](#hyperunique)
    - [cardinality()](#cardinality)
    - [distinctCount()](#distinctcount)    
  - [QueryBuilder: Filters](#querybuilder-filters)
    - [where()](#where)
    - [orWhere()](#orwhere)
    - [whereIn()](#wherein)
    - [whereNotIn()](#wherenotin)
    - [whereBetween()](#wherebetween)
    - [whereNotBetween()](#wherenotbetween)
    - [whereColumn()](#wherecolumn)
    - [whereNotColumn()](#wherenotcolumn)
    - [whereInterval()](#whereinterval)
    - [whereNotInterval()](#wherenotinterval)    
  - [QueryBuilder: Extractions](#querybuilder-extractions)
    - [lookup()](#lookup-extraction)
    - [inlineLookup()](#inlinelookup-extraction)
    - [format()](#format-extraction)
    - [upper()](#upper-extraction)
    - [lower()](#lower-extraction)
    - [timeParse()](#timeparse-extraction)
    - [timeFormat()](#timeformat-extraction)
    - [regex()](#regex-extraction)
    - [partial()](#partial-extraction)
    - [searchQuery()](#searchquery-extraction)
    - [substring()](#substring-extraction)
    - [javascript()](#javascript-extraction)
    - [bucket()](#bucket-extraction)
  - [QueryBuilder: Having Filters](#querybuilder-having-filters)
    - [having()](#having)
    - [orHaving()](#orhaving)
  - [QueryBuilder: Virtual Columns](#querybuilder-virtual-columns)
    - [virtualColumn()](#virtualcolumn)
    - [selectVirtual()](#selectvirtual)
  - [QueryBuilder: Post Aggregations](#querybuilder-post-aggregations)
    - [fieldAccess()](#fieldaccess)
    - [constant()](#constant)
    - [divide()](#divide)
    - [multiply()](#multiply)
    - [subtract()](#subtract)
    - [add()](#add)
    - [quotient()](#quotient)
    - [longGreatest() and doubleGreatest()](#longgreatest-and-doublegreatest)
    - [longLeast() and doubleLeast()](#longleast-and-doubleleast)
    - [postJavascript()](#postjavascript)
    - [hyperUniqueCardinality()](#hyperuniquecardinality)
  - [QueryBuilder: Search Filters](#querybuilder-search-filters)
    - [searchContains()](#searchcontains)
    - [searchFragment()](#searchfragment)
    - [searchRegex()](#searchregex)
  - [QueryBuilder: Execute The Query](#querybuilder-execute-the-query)
    - [execute()](#execute)
    - [groupBy()](#groupby)
    - [topN()](#topn)
    - [selectQuery()](#selectquery)
    - [scan()](#scan)
    - [timeseries()](#timeseries)
    - [search()](#search)
  - [Metadata](#metadata)
    - [intervals](#metadata-intervals)  
    - [interval](#metadata-interval)  
    - [structure](#metadata-structure)
  - [Reindex/compact data](#reindex--compact-data)
    - [compact()](#compact)
    - [reindex()](#reindex)      

# Documentation

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


#### `DruidClient::compact()`

The `compact()` method returns a `CompactTaskBuilder` object which allows you to build a compact task. 

For more information, see [compact()](#compact).

#### `DruidClient::reindex()`

The `compact()` method returns a `IndexTaskBuilder` object which allows you to build a re-index task. 

For more information, see [reindex()](#reindex).

#### `DruidClient::taskStatus()`

The `taskStatus()` method allows you to fetch the status of a task identifier.

For more information and an example, see [reindex()](#reindex) or [compact()](#compact).  

#### `DruidClient::metadata()`

The `metadata()` method returns a `MetadataBuilder` object, which allows you to retrieve metadata from your druid 
instance. See for more information the [Metadata](#metadata) chapter.
   
## QueryBuilder: Generic Query Methods

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

The `limit()` method allows you to limit the result set of the query. 

The Limit can be used for all query types. However, its mandatory for the TopN Query and the Select Query.
  
**NOTE:** It is not possible to limit with an offset, like you can do with an SQL query. If you want to use pagination, 
you can use a Select query. However, the select query does not allow you to aggregate metrics and group by dimensions. 
See the Select Query for more information. 

Example:
```
// Limit the result to 50 rows.
$builder->limit(50);
```

The `limit()` method has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example** | **Description**                                   |
|----------|-----------------------|--------------|-------------|---------------------------------------------------|
| int      | Required              | `$limit`     | 50          | Limit the result to this given number of records. | 


#### `orderBy()`

The `orderBy()` method allows you to order the result in a given way.
This method only applies to **GroupBy** and **TopN** Queries. You should use `orderByDirection()`.

Example:
```php 
$builder
  ->select('channel')
  ->longSum('deleted')
  ->orderBy('deleted', OrderByDirection::DESC)
  ->groupBy();
```

The `orderBy()` method has the following arguments:

| **Type** | **Optional/Required** | **Argument**         | **Example**              | **Description**                                                                                           |
|----------|-----------------------|----------------------|--------------------------|-----------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimensionOrMetric` | "channel"                | The dimension or metric where you want to order by                                                        |
| string   | Required              | `$direction`         | `OrderByDirection::DESC` | The direction or your order. You can use an OrderByDirection constant, or a string like "asc" or "desc".  |
| string   | Optional              | `$sortingOrder`      | `SortingOrder::STRLEN`   | This defines how the sorting is executed.                                                                 |

See for more information about SortingOrders this page: https://druid.apache.org/docs/latest/querying/sorting-orders.html

Please note: this method differs per query type. Please read below how this method workers per Query Type.

**GroupBy Query**

You can call this method multiple times, adding an order-by to the query. 
The GroupBy Query only allows ordering the result if there a limit is given. If you do not supply a limit, we will use 
a default limit of `999999`. 

**TopN Query**

For this query type it is mandatory to call this method. You _should_ call this method with the dimension or metric 
where you want to order your result by.


#### `orderByDirection()`

The `orderByDirection()` method allows you to specify the direction of the order by. This method only applies to the 
TimeSeries, Select and Scan Queries. Use `orderBy()` For GroupBy and TopN Queries.

Example:
```php
$response = $client->query('wikipedia', 'hour')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->longSum('deleted')    
    ->select('__time', 'datetime')
    ->orderByDirection(OrderByDirection::DESC)
    ->timeseries();
```

The `orderByDirection()` method has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example**              | **Description**                                                                                           |
|----------|-----------------------|--------------|--------------------------|-----------------------------------------------------------------------------------------------------------|
| string   | Required              | `$direction` | `OrderByDirection::DESC` | The direction or your order. You can use an OrderByDirection constant, or a string like "asc" or "desc".  |


#### `pagingIdentifier()`

The `pagingIdentifier()` allows you to do paginating on the result set. This only works on SELECT queries. 

When you execute a select query, you will return a paging identifier. To request the next "page", use this paging identifier
in your next request. 

Example:
```php
// Build a select query
$builder = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select(['__time', 'channel', 'user', 'deleted', 'added'])    
    ->limit(10);

// Execute the query for "page 1"
$response1 = $builder->selectQuery();

// Now, request "page 2".
 $builder->pagingIdentifier($response1->getPagingIdentifier());

// Execute the query for "page 2".
$response2 = $builder->selectQuery($context);
```

An paging identifier is an array and looks something like this:
```array (
  'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-26T18:30:14.418Z' => 10,
)
```

The `pagingIdentifier()` method has the following arguments:

| **Type** | **Optional/Required** | **Argument**        | **Example** | **Description**                                   |
|----------|-----------------------|---------------------|-------------|---------------------------------------------------|
| array    | Required              | `$pagingIdentifier` | See above.  | The paging identifier from your previous request. |


#### `subtotals()`

The `subtotals()` method allows you to retrieve your aggregations over various dimensions in your query. This is quite 
similar to the `WITH ROLLUP` mysql logic. 

**NOTE::** This method only applies to groupBy queries!

Example:
```php
// Build a groupBy query with subtotals
$response = $client->query('wikipedia')
    ->interval('2015-09-12 20:00:00', '2015-09-12 22:00:00')
    ->select('__time', 'hour', function (ExtractionBuilder $extractionBuilder) {
        $extractionBuilder->timeFormat('yyyy-MM-dd HH:00:00');
    })
    ->select('namespace')
    ->count('edits')
    ->longSum('added')
    // select all namespaces which begin with Draft.
    ->where('namespace', 'like', 'Draft%')
    ->subtotals([
        ['hour', 'namespace'], // get the results per hour, namespace 
        ['hour'], // get the results per hour
        [] // get the results in total (everything together)
    ])
    ->groupBy();
```

Example response (Note: result is converted to a table for better visibility):
```
+------------+---------------------+-------+-------+
| namespace  | hour                | added | edits | 
+------------+---------------------+-------+-------+
| Draft      | 2015-09-12 20:00:00 | 0     | 1     | 
| Draft talk | 2015-09-12 20:00:00 | 359   | 1     | 
| Draft      | 2015-09-12 21:00:00 | 656   | 1     |
+------------+---------------------+-------+-------+ 
|            | 2015-09-12 20:00:00 | 359   | 2     | 
|            | 2015-09-12 21:00:00 | 656   | 1     |
+------------+---------------------+-------+-------+ 
|            |                     | 1015  | 3     | 
+------------+---------------------+-------+-------+
```

As you can see, the first three records are our result per 'hour' and 'namespace'.<br> 
Then, two records are just per 'hour'. <br>
Finally, the last record is the 'total'. 

The `subtotals()` method has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example**                                | **Description**                                                                                               |
|----------|-----------------------|--------------|--------------------------------------------|---------------------------------------------------------------------------------------------------------------|
| array    | Required              | `$subtotals` | `[ ['country', 'city'], ['country'], [] ]` | An array which contains array's with dimensions where you want to receive your totals for. See example above. |


#### `metrics()`

With the `metrics()` method you can specify which metrics you want to select when you are executing a `selectQuery()`. 

**NOTE:** This only applies to the select query type!

Example:
```php
$result = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select(['__time', 'channel', 'user'])
    ->metrics(['deleted', 'added'])
    ->selectQuery();
```

The `metrics()` method has the following arguments: 

| **Type** | **Optional/Required** | **Argument** | **Example**            | **Description**                                                 |
|----------|-----------------------|--------------|------------------------|-----------------------------------------------------------------|
| array    | Required              | `$metrics`   | `['added', 'deleted']` | Array of metrics which you want to select in your select query. |


#### `dimensions()`

With the `dimensions()` method you can specify which dimensions should be used for a Search Query. 

**NOTE:** This only applies to the search query type! See also the [Search](#search) query. This method should not
be confused with selecting dimensions for your other query types. See [Dimension Selections](#dimension-selections) for
more information about selecting dimensions for your query.

```php
// Build a Search Query
$response = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->dimensions(['namespace', 'channel']) 
    ->searchContains('wikipedia')
    ->search();
```

The `dimensions()` method has the following arguments: 

| **Type** | **Optional/Required** | **Argument**  | **Example**           | **Description**                                  |
|----------|-----------------------|---------------|-----------------------|--------------------------------------------------|
| array    | Required              | `$dimensions` | `['name', 'address']` | Array of dimensions where you want to search in. |


#### `toArray()`

The `toArray()` method will try to build the query. We will try to auto detect the best query type. After that, we will build
the query and return the query as an array.

Example:
```php
$builder = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select(['__time', 'channel', 'user', 'deleted', 'added'])    
    ->limit(10);

// Show the query as an array
print_r($builder->toArray());
```

The `toArray()` method has the following arguments:

| **Type**           | **Optional/Required** | **Argument** | **Example**        | **Description**           |
|--------------------|-----------------------|--------------|--------------------|---------------------------|
| array/QueryContext | Optional              | `$context`   | ['priority' => 75] | Query context parameters. |


#### `toJson()`

The `toJson()` method will try to build the query. We will try to auto detect the best query type. After that, we will build
the query and return the query as a JSON string.

Example:
```php
$builder = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select(['__time', 'channel', 'user', 'deleted', 'added'])    
    ->limit(10);

// Show the query as an array
var_export($builder->toJson());
```

The `toJson()` method has the following arguments:

| **Type**           | **Optional/Required** | **Argument** | **Example**        | **Description**           |
|--------------------|-----------------------|--------------|--------------------|---------------------------|
| array/QueryContext | Optional              | `$context`   | ['priority' => 75] | Query context parameters. |

## QueryBuilder: Dimension Selections

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

## QueryBuilder: Metric Aggregations

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
$builder->first('device');
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
$builder->last('email');
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

This page also explains the usage of hyperUniqe very well:
https://cleanprogrammer.net/getting-unique-counts-from-druid-using-hyperloglog/

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
  
  
  
## QueryBuilder: Filters

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


#### `whereColumn()`

The `whereColumn()` filter compares two dimensions with each other. Only records where the dimensions match will be returned.

You can supply the dimension name as a string, or you can build a more advanced dimension (with for example an extraction 
filter) using a Closure function. Example:

```php
// Select records where "initials" is equal to the first character of "first_name".
$builder->whereColumn('initials', function(DimensionBuilder $dimensionBuilder) {
  $dimensionBuilder->select('first_name', function(ExtractionBuilder $extractionBuilder) {
    $extractionBuilder->substring(0, 1);
  });
});
```

The `whereColumn()` filter has the following arguments:

| **Type**       | **Optional/Required** | **Argument**  | **Example**  | **Description**                                                                                                                                             |
|----------------|-----------------------|---------------|--------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string/Closure | Required              | `$dimensionA` | "initials"   | The dimension which you want to compare, or a Closure which will receive a `DimensionBuilder` which allows you to select a dimension in a more advance way. |
| string/Closure | Required              | `$dimensionB` | "first_name" | The dimension which you want to compare, or a Closure which will receive a `DimensionBuilder` which allows you to select a dimension in a more advance way. |


#### `whereNotColumn()`

The `whereNotColumn()` filter works exactly the same as the `whereColumn()` filter, only now it will only return rows
where `$dimensionA` is different then `$dimensionB`.  


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


## QueryBuilder: Extractions

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


#### `lookup()` extraction

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


#### `inlineLookup()` extraction

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


#### `format()` extraction

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


#### `upper()` extraction

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


#### `lower()` extraction

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


#### `timeParse()` extraction

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


#### `timeFormat()` extraction

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


#### `regex()` extraction

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


#### `partial()` extraction

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


#### `searchQuery()` extraction

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


#### `substring()` extraction

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


#### `javascript()` extraction 

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
$binaryFlagToMatch = 16;

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


#### `bucket()` extraction

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


## QueryBuilder: Having Filters

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


## QueryBuilder: Virtual Columns

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


## QueryBuilder: Post Aggregations

Post aggregations are aggregations which are executed after the result is fetched from the druid database.


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

## QueryBuilder: Search Filters

Search filters are filters which are only used for a search query. They allow you to specify which filter should be applied
to the given dimensions.

There are a few different filters available:

#### `searchContains()`

The `searchContains()` method allows you to filter on dimensions where the dimension contains your given value. You can specify
if the match should be case sensitive or not. 

Example:

```php
// Build a Search Query using a "contains" filter
$response = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->dimensions(['namespace'])
    ->searchContains('Wikipedia', true) // case sensitive!
    ->search();
```

The `searchContains()` method has the following arguments:

| **Type** | **Optional/Required** | **Argument**     | **Example** | **Description**                                                               |
|----------|-----------------------|------------------|-------------|-------------------------------------------------------------------------------|
| string   | Required              | `$value`         | "wikipedia" | Rows will be returned if the dimension(s) contain this value.                 |
| bool     | Optional              | `$caseSensitive` | true        | Set to true for case sensitive matching, false for case insensitive matching. |


#### `searchFragment()` 

The `searchFragment()` method allows you to filter on dimensions where the dimension contains ALL of the given string values.
You can specify if the match should be case sensitive or not.

Example:

```php
// Build a Search Query using a "fragment" filter.
$response = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->dimensions(['page'])
    ->searchFragment(['United', 'States'], true) // case sensitive!     
    ->search();
```

The `searchFragment()` method has the following arguments:

| **Type** | **Optional/Required** | **Argument**     | **Example**       | **Description**                                                                            |
|----------|-----------------------|------------------|-------------------|--------------------------------------------------------------------------------------------|
| array    | Required              | `$values`        | ["wiki", "pedia"] | An array with strings. Only dimensions which contain ALL of the given values are returned. |
| bool     | Optional              | `$caseSensitive` | true              | Set to true for case sensitive matching, false for case insensitive matching.              |


#### `searchRegex()`

The `searchRegex()` method allows you to filter on dimensions where the dimension matches the given regular expression.

See this page for more information about regular expressions: https://docs.oracle.com/javase/6/docs/api/java/util/regex/Pattern.html

Example:

```php
// Build a Search Query using a "regex" filter.
$response = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->dimensions(['page'])
    ->searchRegex('^Wiki')      
    ->search();
```

The `searchRegex()` method has the following arguments:

| **Type** | **Optional/Required** | **Argument**     | **Example** | **Description**                                                |
|----------|-----------------------|------------------|-------------|----------------------------------------------------------------|
| string   | Required              | `$pattern`       | "^Wiki"     | A regular expression where the dimension should match agains.  |


## QueryBuilder: Execute The Query

The following methods allow you to execute the query which you have build using the other methods. There are various
query types available, or you can use the `execute()` method which tries to detect the best query type for your query.

#### `execute()`

This method will analyse the data which you have supplied in the query builder, and try to use the best suitable query 
type for you. If you do not want to use the "internal logic", you should use one of the methods below. 

```php 
$response = $builder
  ->select('channel')
  ->longSum('deleted')
  ->orderBy('deleted', OrderByDirection::DESC)
  ->execute();
```

The `execute()` method has the following arguments:

| **Type**           | **Optional/Required** | **Argument** | **Example**        | **Description**           |
|--------------------|-----------------------|--------------|--------------------|---------------------------|
| array/QueryContext | Optional              | `$context`   | ['priority' => 75] | Query context parameters. |

You can supply an array with context parameters, or use a `QueryContext` object (or any context object which is related 
to the query type of your choice, like a `ScanQueryContext`). For more information about query specific context, see the 
query descriptions below.

The `QueryContext()` object contains context properties which apply to all queries.

**Response** 

The response of this method is dependent of the query which is executed. Each query has it's own response object. However,
all query responses are extended of the `QueryResponse` object. Each query response has therefor a `$response->raw()` method 
which will return an array with the raw data returned by druid. There is also an `$response->data()` method which 
returns the data in a "normalized" way so that it can be directly used. 

#### `groupBy()`

The `groupBy()` method will execute your build query as a GroupBy query.

This the most commonly used query type. However, it is not the quickest. If you are doing aggregations with time as your 
only grouping, or an ordered groupBy over a single dimension, consider Timeseries and TopN queries as well as groupBy. 

For more information, see this page: https://druid.apache.org/docs/latest/querying/groupbyquery.html

With the GroupBy query you can aggregate metrics and group by the dimensions which you have selected.

Example:
```php
$builder = $client->query('wikipedia', Granularity::HOUR);

$result = $builder 
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select(['namespace', 'page'])    
    ->count('edits')
    ->longSum('added')
    ->longSum('deleted')
    ->where('isRobot', 'false')
    ->groupBy();
```

There are two different strategies to execute a GroupBy query. V2 which is the current default, and V1, which is the legacy
strategy. When execute a query using the `groupBy()` method, the v2 strategy is used. If you want to use the v1 strategy,
you can make use of the method `groupByV1()`. This method works the same, only uses the v1 strategy to execute the query.  

For more information about groupBy strategies see this page: 
https://druid.apache.org/docs/latest/querying/groupbyquery.html#implementation-details

The `groupBy()` method and the `groupByV1()` method have the following arguments:

| **Type**           | **Optional/Required** | **Argument** | **Example**        | **Description**                                           |
|--------------------|-----------------------|--------------|--------------------|-----------------------------------------------------------|
| array/QueryContext | Optional              | `$context`   | ['priority' => 75] | Query context parameters. See below for more information. |

**Context**

The `groupBy()` method accepts 1 parameter, the query context. This can be given as an array with key => value pairs,
or an `GroupByV2QueryContext` object.

The context allows you to change the behaviour of the query execution. There is a difference between the available 
context parameters between the v1 and the v2 query strategy. If you use  `groupByV1()`, then you should also use the 
`GroupByV1QueryContext`.

Example using query context:

```php
$builder = $client->query('wikipedia', Granularity::HOUR);

$builder 
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select(['namespace', 'page'])    
    ->count('edits')
    ->longSum('added')
    ->longSum('deleted')
    ->where('isRobot', 'false');

// Create the query context 
$context = new GroupByV2QueryContext();
$context->setNumParallelCombineThreads(5);

// Execute the query using the query context.
$result = $builder->groupBy($context);
```

**Response**

The response of this query will be an `GroupByQueryResponse` (this applies for both query strategies). <br>
The `$response->raw()` method will return an array with the raw data returned by druid. <br>
The `$response->data()` method returns the data as an array in a "normalized" way so that it can be directly used. 

#### `topN()`  

The `topN()` method will execute your query as an TopN query. TopN queries return a sorted set of results for the values 
in a given dimension according to some criteria. 

For more information about topN queries, see this page: https://druid.apache.org/docs/latest/querying/topnquery.html

Example:
```php
$response = $client->query('wikipedia', 'all')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select('channel')
    ->count('edited')
    ->limit(10)
    ->orderBy('edited', 'desc')
    ->topN();
```

The `topN()` method has the following arguments:

| **Type**           | **Optional/Required** | **Argument** | **Example**        | **Description**                                           |
|--------------------|-----------------------|--------------|--------------------|-----------------------------------------------------------|
| array/QueryContext | Optional              | `$context`   | ['priority' => 75] | Query context parameters. See below for more information. |

**Context**

The `topN()` method receives 1 parameter, the query context. The query context is either an array with key => value pairs,
or an `TopNQueryContext` object. The context allows you to change the behaviour of the query execution.

Example:
```php
$builder = $client->query('wikipedia', 'all')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select('channel')
    ->count('edited')
    ->limit(10)
    ->orderBy('edited', 'desc');

// Create specific query context for our query
$context = new TopNQueryContext();
$context->setMinTopNThreshold(1000);

// Execute the query
$response = $builder->topN($context);
```

**Response**

The response of this query will be an `TopNQueryResponse`. <br>
The `$response->raw()` method will return an array with the raw data returned by druid. <br>
The `$response->data()` method returns the data as an array in a "normalized" way so that it can be directly used. 

#### `selectQuery()`

The `selectQuery()` method will execute your query as an select query. It's important to not mix up this method with the
`select()` method, which will select dimensions for your query.

The `selectQuery()` returns raw druid data. It does not allow you to aggregate metrics. It _does_ support pagination. 

However, it is encouraged to use the Scan query type rather than Select whenever possible. 
In situations involving larger numbers of segments, the Select query can have very high memory and performance overhead. 
The Scan query does not have this issue. The major difference between the two is that the Scan query does not support 
pagination. However, the Scan query type is able to return a virtually unlimited number of results even without 
pagination, making it unnecessary in many cases.

For more information, see: https://druid.apache.org/docs/latest/querying/select-query.html

Example:
```php
// Build a select query
$builder = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select(['__time', 'channel', 'user', 'deleted', 'added'])
    ->orderByDirection(OrderByDirection::DESC)
    ->limit(10);

// Execute the query.
$response = $builder->selectQuery($context);

// ... Use your respone (page 1) here! ...

// echo "Identifier for page 2: " . var_export($response->pagingIdentifier(), true) . "\n\n";

// Now, request "page 2".
$builder->pagingIdentifier($response->pagingIdentifier());

// Execute the query.
$response = $builder->selectQuery($context);

// ... Use your response (page 2) here! ...
``` 

The `selectQuery()` method has the following arguments:

| **Type**           | **Optional/Required** | **Argument** | **Example**        | **Description**                                           |
|--------------------|-----------------------|--------------|--------------------|-----------------------------------------------------------|
| array/QueryContext | Optional              | `$context`   | ['priority' => 75] | Query context parameters. See below for more information. |

**Context**

The `selectQuery()` method receives 1 parameter, the query context. The query context is either an array with key => value pairs,
or an `QueryContext` object. There is no SelectQueryContext, as there are no context parameters specific for this query type.
The context allows you to change the behaviour of the query execution.

Example:

```php
// Example of setting query context. It can also be supplied as an array in the selectQuery() method call.
$context = new QueryContext();
$context->setPriority(100);

// Execute the query.
$response = $builder->selectQuery($context);
```

**Response**

The response of this query will be an `SelectQueryResponse`. <br>
The `$response->raw()` method will return an array with the raw data returned by druid. <br> 
The `$response->data()` method  returns the data as an array in a "normalized" way so that it can be directly used. <br>
The `$response->pagingIdentifier()` method returns paging identifier. The paging identifier will be something like this:

```
Array(
    'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z' => 19
)
```

#### `scan()`

The `scan()` method will execute your query as a scan query. The Scan query returns raw Apache Druid (incubating) rows 
in streaming mode. The biggest difference between the Select query and the Scan query is that the Scan query does not 
retain all the returned rows in memory before they are returned to the client. The Select query will retain the rows 
in memory, causing memory pressure if too many rows are returned. The Scan query can return all the rows without 
issuing another pagination query.
                                                             
For more information see this page: https://druid.apache.org/docs/latest/querying/scan-query.html

Example:
```php
// Build a scan query
$builder = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select(['__time', 'channel', 'user', 'deleted', 'added'])
    ->orderByDirection(OrderByDirection::DESC)
    ->limit(10);

// Execute the query.
$response = $builder->scan();
```

the `scan()` method has the following parameters:

| **Type**           | **Optional/Required** | **Argument**    | **Example**                        | **Description**                                                                                                                                                                                 |
|--------------------|-----------------------|-----------------|------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| array/QueryContext | Optional              | `$context`      | ['priority' => 75]                 | Query context parameters. See below for more information.                                                                                                                                       |
| int                | Optional              | `$rowBatchSize` | 20480                              | How many rows buffered before return to client. Default is 20480                                                                                                                                |
| bool               | Optional              | `$legacy`       | false                              | Return results consistent with the legacy "scan-query" contrib extension. Defaults to the value set by druid.query.scan.legacy, which in turn defaults to `false`. See Legacy mode for details. |
| string             | Optional              | `$resultFormat` | ScanQueryResultFormat::NORMAL_LIST | Result Format. Use one of the ScanQueryResultFormat::* constants.                                                                                                                               |

**Context**

The first parameter of the `scan()` method is the query context. The query context is either an array with key => value pairs,
or an `ScanQueryContext` object. The context allows you to change the behaviour of the query execution.

Example:

```php
// Example of setting query context. It can also be supplied as an array in the scan() method call.
$context = new ScanQueryContext();
$context->setPriority(100);
$context->setMaxRowsQueuedForOrdering(5000);

// Execute the query.
$response = $builder->scan($context);
```

**Response**

The response of this query will be an `ScanQueryResponse`. <br>
The `$response->raw()` method will return an array with the raw data returned by druid. <br> 
The `$response->data()` method  returns the data as an array in a "normalized" way so that it can be directly used. 

**ScanQueryResultFormat**

You can specify two result formats: 

| **Format**                              | **Description**                                        |
|-----------------------------------------|--------------------------------------------------------|
| `ScanQueryResultFormat::NORMAL_LIST`    | This will return the data including the field names.   |
| `ScanQueryResultFormat::COMPACTED_LIST` | This will return the data, but without the fieldnames. |

Example `$response->data()` for `ScanQueryResultFormat::NORMAL_LIST`:
```
array (
  0 => 
  array (
    'timestamp' => '2015-09-12T23:59:59.200Z',
    '__time' => 1442102399200,
    'channel' => '#en.wikipedia',
    'user' => 'Eva.pascoe',
    'deleted' => 0,
    'added' => 182,
  ),
)
```
Example `$response->data()` for `ScanQueryResultFormat::COMPACTED_LIST`:
```
array (
  0 => 
  array (
    0 => '2015-09-12T23:59:59.200Z',
    1 => 1442102399200,
    2 => '#en.wikipedia',
    3 => 'Eva.pascoe',
    4 => 0,
    5 => 182,
  ),  
)
``` 

#### `timeseries()`

The `timeseries()` method executes your query as a TimeSeries query. It will return the data grouped by the given 
time granularity. 

For more information about the TimeSeries query, see this page: https://druid.apache.org/docs/latest/querying/timeseriesquery.html

Example:

```php
// Build a TimeSeries query
$builder = $client->query('wikipedia', Granularity::HOUR)
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->longSum('added')
    ->longSum('deleted')
    ->count('edited')
    ->select('__time', 'datetime')
    ->orderByDirection(OrderByDirection::DESC);

// Execute the query.
$response = $builder->timeseries();
```

The `timeseries()` method has the following arguments:

| **Type**           | **Optional/Required** | **Argument** | **Example**        | **Description**                                           |
|--------------------|-----------------------|--------------|--------------------|-----------------------------------------------------------|
| array/QueryContext | Optional              | `$context`   | ['priority' => 75] | Query context parameters. See below for more information. |

**Context**

The `timeseries()` method receives 1 parameter, the query context. The query context is either an array with key => value pairs,
or an `TimeSeriesQueryContext` object. 
The context allows you to change the behaviour of the query execution.

Example:

```php
// Example of setting query context. It can also be supplied as an array in the timeseries() method call.
$context = new TimeSeriesQueryContext();
$context->setSkipEmptyBuckets(true);

// Execute the query.
$response = $builder->timeseries($context);
```

**Response**

The response of this query will be an `TimeSeriesQueryResponse`. <br>
The `$response->raw()` method will return an array with the raw data returned by druid. <br> 
The `$response->data()` method  returns the data as an array in a "normalized" way so that it can be directly used. 


#### `search()`

The `search()` method executes your query as a Search Query. A Search Query will return the unqiue values of a dimension 
which matches a specific search selection. The response will be containing the dimension which matched your search 
criteria, the value of your dimension and the number of occurrences.  

**Note:** You should not mix up this method with the [searchQuery()](#searchquery) extraction filter.

For more information about the Search Query, see this page: https://druid.apache.org/docs/latest/querying/searchquery.html

See the [Search Filters](#search-filters) for examples how to specify your search filter.

Example:

```php
// Build a Search Query
$builder = $client->query('wikipedia')
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->dimensions(['namespace']) // If left out, all dimensions are searched
    ->searchContains('wikipedia')
    ->limit(150);

// Execute the query, sorting by String Length (shortest first).
$response = $builder->search([], SortingOrder::STRLEN);
```

The `search()` method has the following arguments:

| **Type**           | **Optional/Required** | **Argument**    | **Example**            | **Description**                                           |
|--------------------|-----------------------|-----------------|------------------------|-----------------------------------------------------------|
| array/QueryContext | Optional              | `$context`      | ['priority' => 75]     | Query context parameters. See below for more information. |
| string             | Optional              | `$sortingOrder` | `SortingOrder::STRLEN` | This defines how the sorting is executed.                 |

**Context**

The `search()` method receives as first parameter the query context. The query context is either an array with key => value pairs,
or an `QueryContext` object. The context allows you to change the behaviour of the query execution.

Example:

```php
// Example of setting query context. It can also be supplied as an array in the search() method call.
$context = new QueryContext();
$context->setPriority(100);

// Execute the query.
$response = $builder->search($context);
```

**Response**

The response of this query will be an `SearchQueryResponse`. <br>
The `$response->raw()` method will return an array with the raw data returned by druid. <br> 
The `$response->data()` method  returns the data as an array in a "normalized" way so that it can be directly used. 


## Metadata

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

## Reindex / compact data

Druid stores data in segments. When you want to update some data, you have to rebuild the _whole_ segment.
Therefore we use smaller segments when the data is still "fresh". The change of data needed to be rebuild is the biggest
when it is fresh. In this way, we only need to rebuild 1 hour of data, instead for a whole month or such. 

We use for example hour segments for "today" and "yesterday", and we have some processes which will change this data into
bigger segments after that. 

Reindexing and compacting data is therefor very important to us. Here we show you how you can use this.

#### `compact()`

With the `compact()` method you can create a compaction task. A compact task can be used to change the segment size of 
your existing data.   
A compaction task internally generates an index task for performing compaction work with some fixed parameters.  

See for more information this page: https://druid.apache.org/docs/latest/ingestion/data-management.html#compact 

Example:
```php
$client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

// Build our compact task.
$taskId = $client->compact('wikipedia')
    ->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z ')
    ->segmentGranularity(Granularity::DAY) // set our new segment size (it was for example "hour")
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
print_r($status->data());
```

The `compact` method will return a `CompactTaskBuilder` object which allows you to specify the rest of the 
required data. 

**NOTE:** We currently do not have support for building metricSpec and DimensionSpec yet. 

#### `reindex()`

With the `reindex()` method you can re-index data which is already in a druid dataSource. You can do a bit more then 
with the `compact()` method. 

For example, you can filter or transform existing data or change the query granularity: 

```php
$client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

// Build our reindex task
$taskId = $client->reindex('wikipedia-new')
    ->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z ')
    ->parallel()
    ->fromDataSource('wikipedia') 
    ->segmentGranularity(Granularity::DAY)
    ->queryGranularity(Granularity::HOUR)
    ->rollup()
    ->transform(function (\Level23\Druid\Transforms\TransformBuilder $builder) {
        $builder->transform('"true"', 'isRobot');
        $builder->where('comment', 'like', '%Robot%');
    })
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
print_r($status->data());
```
The `reindex` method will return a `IndexTaskBuilder` object which allows you to specify the rest of the 
required data. By default we will use a `IngestSegmentFirehose` to ingest data from an existing data source. 

If you want you can change the data source where the data is read from using the `fromDataSource()` method. 

**NOTE:** Currently we only support re-indexing, and thus the IngestSegment Firehose. 

 
