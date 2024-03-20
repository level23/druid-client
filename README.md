# Druid-Client

[![Build](https://github.com/level23/druid-client/actions/workflows/build.yml/badge.svg)](https://github.com/level23/druid-client/actions/workflows/build.yml)
[![Coverage Status](https://coveralls.io/repos/github/level23/druid-client/badge.svg?branch=master)](https://coveralls.io/github/level23/druid-client?branch=master)
[![Packagist Version](https://img.shields.io/packagist/v/level23/druid-client.svg)](https://packagist.org/packages/level23/druid-client)
[![Total Downloads](https://img.shields.io/packagist/dt/level23/druid-client.svg)](https://packagist.org/packages/level23/druid-client)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/level23/druid-client)](https://scrutinizer-ci.com/g/level23/druid-client)
[![Software License](https://img.shields.io/badge/license-Apache%202.0-brightgreen.svg)](LICENSE.txt)

The goal of this project is to make it easy to select data from druid.

This project gives you an easy query builder to create the complex druid queries.

It also gives you a way to manage dataSources (tables) in druid and import new data from files.

## Requirements

This package only requires Guzzle as dependency. The PHP and Guzzle version requirements are listed below.

| Druid Client Version | PHP Requirements                        | Guzzle Requirements | Druid Requirements |
|----------------------|-----------------------------------------|---------------------|--------------------|
| 4.* (current)        | PHP 8.2 or higher                       | Version 7.0         | >= 28.0.0          |
| 3.*                  | PHP 8.2 or higher                       | Version 7.0         |                    |
| 2.*                  | PHP 7.4, 8.0, 8.1 and 8.2.              | Version 6.2 or 7.*  |                    |
| 1.*                  | PHP version 7.2, 7.4, 8.0, 8.1 and 8.2. | Version 6.2 or 7.*  |                    |

## Installation

To install this package, you can use composer:

```
composer require level23/druid-client
```

You can also download it as a ZIP file and include it in your project, as long as you have Guzzle also in your project.

## ChangeLog and Upgrading

See [CHANGELOG](CHANGELOG.md) for changes in the different versions and how to upgrade to the latest version.

## Laravel/Lumen support.

This package is Laravel/Lumen ready. It can be used in a Laravel/Lumen project, but it's not required.

#### Laravel

For Laravel the package will be auto discovered.

#### Lumen

If you are using a Lumen project, just include the service provider
in `bootstrap/app.php`:

```php
// Register the druid-client service provider
$app->register(Level23\Druid\DruidServiceProvider::class);
```

#### Laravel/Lumen Configuration:

You should also define the correct endpoint URL's in your `.env` in your Laravel/Lumen project:

```
DRUID_BROKER_URL=http://broker.url:8082
DRUID_COORDINATOR_URL=http://coordinator.url:8081
DRUID_OVERLORD_URL=http://overlord.url:8090
DRUID_RETRIES=2
DRUID_RETRY_DELAY_MS=500
DRUID_TIMEOUT=60
DRUID_CONNECT_TIMEOUT=10
DRUID_POLLING_SLEEP_SECONDS=2
```

If you are using a Druid Router process, you can also just set the router url, which then will be used for the broker,
overlord and the coordinator:

```
DRUID_ROUTER_URL=http://druid-router.url:8080
```

## Todo's

- Support for building metricSpec and DimensionSpec in CompactTaskBuilder
- Implement hadoop based batch ingestion (indexing)
- Implement Avro Stream and Avro OCF input formats.
## Examples

There are several examples which are written on the single-server tutorial of druid. See [this](examples/README.md) page
for more information.

# Table of Contents

- [DruidClient](#druidclient)
    - [DruidClient::auth()](#druidclientauth)
    - [DruidClient::query()](#druidclientquery)
    - [DruidClient::cancelQuery()](#druidclientcancelquery)
    - [DruidClient::compact()](#druidclientcompact)
    - [DruidClient::reindex()](#druidclientreindex)
    - [DruidClient::pollTaskStatus()](#druidclientpolltaskstatus)
    - [DruidClient::taskStatus()](#druidclienttaskstatus)
    - [DruidClient::metadata()](#druidclientmetadata)
    - [QueryBuilder: Generic Query Methods](#querybuilder-generic-query-methods)
        - [interval()](#interval)
        - [limit()](#limit)
        - [orderBy()](#orderby)
        - [orderByDirection()](#orderbydirection)
        - [pagingIdentifier()](#pagingidentifier)
        - [subtotals()](#subtotals)
        - [metrics()](#metrics)
        - [dimensions()](#dimensions)
        - [toArray()](#toarray)
        - [toJson()](#tojson)
    - [QueryBuilder: Data Sources](#querybuilder-data-sources)
        - [from()](#from)
        - [join()](#join)
        - [leftJoin()](#leftjoin)
        - [innerJoin()](#innerjoin)
        - [joinLookup()](#joinlookup)
        - [union()](#union)
    - [QueryBuilder: Dimension Selections](#querybuilder-dimension-selections)
        - [select()](#select)
        - [lookup()](#lookup)
        - [inlineLookup()](#inlinelookup)
        - [multiValueListSelect()](#multivaluelistselect)
        - [multiValueRegexSelect()](#multivalueregexselect)
        - [multiValuePrefixSelect()](#multivalueprefixselect)
    - [QueryBuilder: Metric Aggregations](#querybuilder-metric-aggregations)
        - [count()](#count)
        - [sum()](#sum)
        - [min()](#min)
        - [max()](#max)
        - [first()](#first)
        - [last()](#last)
        - [any()](#any)
        - [javascript()](#javascript)
        - [hyperUnique()](#hyperunique)
        - [cardinality()](#cardinality)
        - [distinctCount()](#distinctcount)
        - [doublesSketch()](#doublesSketch)
    - [QueryBuilder: Filters](#querybuilder-filters)
        - [where()](#where)
        - [orWhere()](#orwhere)
        - [whereNot()](#wherenot)
        - [orWhereNot()](#orwherenot)
        - [whereNull()](#wherenull)
        - [orWhereNull()](#orwherenull)
        - [whereIn()](#wherein)
        - [orWhereIn()](#orwherein)
        - [whereArrayContains()](#wherearraycontains)
        - [orWhereArrayContains()](#orwherearraycontains) 
        - [whereBetween()](#wherebetween)
        - [orWhereBetween()](#orwherebetween)
        - [whereColumn()](#wherecolumn)
        - [orWhereColumn()](#orwherecolumn)
        - [whereInterval()](#whereinterval)
        - [orWhereInterval()](#orwhereinterval)
        - [whereFlags()](#whereflags)
        - [orWhereFlags()](#orwhereflags)
        - [whereExpression()](#whereexpression)
        - [orWhereExpression()](#orwhereexpression)
        - [whereSpatialRectangular()](#wherespatialrectangular)
        - [whereSpatialRadius()](#wherespatialradius)
        - [whereSpatialPolygon()](#wherespatialpolygon)
        - [orWhereSpatialRectangular()](#orwherespatialrectangular)
        - [orWhereSpatialRadius()](#orwherespatialradius)
        - [orWhereSpatialPolygon()](#orwherespatialpolygon)
    - [QueryBuilder: Having Filters](#querybuilder-having-filters)
        - [having()](#having)
        - [orHaving()](#orhaving)
    - [QueryBuilder: Virtual Columns](#querybuilder-virtual-columns)
        - [virtualColumn()](#virtualcolumn)
        - [selectVirtual()](#selectvirtual)
    - [QueryBuilder: Post Aggregations](#querybuilder-post-aggregations)
        - [fieldAccess()](#fieldaccess)
        - [constant()](#constant)
        - [expression](#expression)
        - [divide()](#divide)
        - [multiply()](#multiply)
        - [subtract()](#subtract)
        - [add()](#add)
        - [quotient()](#quotient)
        - [longGreatest() and doubleGreatest()](#longgreatest-and-doublegreatest)
        - [longLeast() and doubleLeast()](#longleast-and-doubleleast)
        - [postJavascript()](#postjavascript)
        - [hyperUniqueCardinality()](#hyperuniquecardinality)
        - [quantile()](#quantile)
        - [quantiles()](#quantiles)
        - [histogram()](#histogram)
        - [rank()](#rank)
        - [cdf()](#cdf)
        - [sketchSummary()](#sketchsummary)
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
        - [timeBoundary](#metadata-timeboundary)
        - [dataSources](#metadata-datasources)
        - [rowCount](#metadata-rowcount)
    - [Reindex/compact data/kill](#reindex--compact-data--kill)
        - [compact()](#compact)
        - [reindex()](#reindex)
        - [kill()](#kill)
    - [Importing data using a batch index job](#importing-data-using-a-batch-index-job)
    - [Input Sources](#input-sources)
        - [AzureInputSource](#azureinputsource)
        - [GoogleCloudInputSource](#googlecloudinputsource)
        - [S3InputSource](#s3inputsource)
        - [HdfsInputSource](#hdfsinputsource)
        - [HttpInputSource](#httpinputsource)
    - [Input Formats](#input-formats)
        - [csvFormat()](#csvformat)
        - [tsvFormat()](#tsvformat)
        - [jsonFormat()](#jsonformat)
        - [orcFormat()](#orcformat)
        - [parquetFormat()](#parquetformat)
        - [protobufFormat()](#protobufformat)

# Documentation

Here is an example of how you can use this package.

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
    // Select a dimension, but change its value using a lookup function.
    ->lookup('carrier_title', 'mccmnc', 'carrierName', 'Unknown')
    // Select a dimension, but use an expression to change the value.
    ->selectVirtual("timestamp_format(__time, 'yyyy-MM-dd HH:00:00')", 'hour')
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
    // Where filters using Closures are supported.
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

| **Type**            | **Optional/Required** | **Argument** | **Example**                         | ** Description**                                                                                                                       |
|---------------------|-----------------------|--------------|-------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| array               | Required              | `$config`    | `['router_url' => 'http://my.url']` | The configuration which is used for this DruidClient. This configuration contains the endpoints where we should send druid queries to. |
| `GuzzleHttp\Client` | Optional              | `$client`    | See example below                   | If given, we will this Guzzle Client for sending queries to your druid instance. This allows you to control the connection.            |

For a complete list of configuration settings take a look at the default values which are defined in the
`$config` property in the DruidClient class.

This class supports some newer functions of Druid. To make sure your server supports these functions, it is recommended
to supply the `version` config setting.

By default, we will use a guzzle client for handing the connection between your application and the druid server. If you
want to change this, for example because you want to use a proxy, you can do this with a custom guzzle client.

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

#### `DruidClient::auth()`

If you have configured your Druid cluster with authentication, you can supply your username/password with this method.
The username/password will be sent in the requests as HTTP Basic Auth parameters.

See also: https://druid.apache.org/docs/latest/operations/auth/

The `auth()` method has 2 parameters:

| **Type** | **Optional/Required** | **Argument** | **Example** | **Description**                       |
|----------|-----------------------|--------------|-------------|---------------------------------------|
| string   | Required              | `$username`  | "foo"       | The username used for authentication. |
| string   | Required              | `$password`  | "bar"       | The password used for authentication. |

You can also overwrite the client and use your own mechanism. See [DruidClient](#druidclient).

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

| **Type** | **Optional/Required** | **Argument**   | **Example** | **Description**                                                                                                                                                                                                                                                                                                                                                     |
|----------|-----------------------|----------------|-------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Optional              | `$dataSource`  | "wikipedia" | The name of the dataSource (table) which you want to query.                                                                                                                                                                                                                                                                                                         |
| string   | Optional              | `$granularity` | "all"       | The granularity which you want to use for this query. You can think of this like an extra "group by" per time window. The results will be grouped by this time window. By default we will use "all", which will return the resultSet in 1 set. Valid values are: all, none, second, minute, fifteen_minute, thirty_minute, hour, day, week, month, quarter and year |

The QueryBuilder allows you to select dimensions, aggregate metric data, apply filters and having filters, etc.

When you do not specify the dataSource, you need to specify it later on your query builder. There are various methods
to do this. See [QueryBuilder: Data Sources](#querybuilder-data-sources)

See the following chapters for more information about the query builder.

#### `DruidClient::cancelQuery()`

The `cancelQuery()` method gives you the ability to cancel a query. To cancel a query, you must know its unique
identifier.
When you execute a query, you can specify the unique identifier yourself in the query context.

Example:

```php
$client = new DruidClient(['router_url' => 'https://router.url:8080']);

// For example, this returns my-query6148716d3772c
$queryId = uniqid('my-query');

// Please note: this will be blocking until we have got result from druid.
// So cancellation has to be done within another php process. 
$result = $client
    ->query('wikipedia', Granularity::DAY) 
    ->interval('2015-09-12 00:00:00', '2015-09-13 00:00:00')
    ->select(['namespace', 'page'])
    ->execute(['queryId' => $queryId]);
```

You can now cancel this query within another process. If you for example store the running queries somewhere,
you can "stop" the running queries by executing this:

```php
$client->cancelQuery('my-query6148716d3772c')
```

The query method has 1 parameter:

| **Type** | **Optional/Required** | **Argument**  | **Example** | **Description**                                                   |
|----------|-----------------------|---------------|-------------|-------------------------------------------------------------------|
| string   | Required              | `$identifier` | "myqueryid" | The unique query identifier which was given in the query context. |

If the cancellation fails, the method will throw an exception. Otherwise, it will not return any result.

See also:
https://druid.apache.org/docs/latest/querying/querying.html#query-cancellation

#### `DruidClient::compact()`

The `compact()` method returns a `CompactTaskBuilder` object which allows you to build a compact task.

For more information, see [compact()](#compact).

#### `DruidClient::reindex()`

The `compact()` method returns a `IndexTaskBuilder` object which allows you to build a re-index task.

For more information, see [reindex()](#reindex).

#### `DruidClient::taskStatus()`

The `taskStatus()` method allows you to fetch the status of a task identifier.

For more information and an example, see [reindex()](#reindex) or [compact()](#compact).

#### `DruidClient::pollTaskStatus()`

The `pollTaskStatus()` method allows you to wait until the status of a task is other than `RUNNING`.

For more information and an example, see [reindex()](#reindex) or [compact()](#compact).

#### `DruidClient::metadata()`

The `metadata()` method returns a `MetadataBuilder` object, which allows you to retrieve metadata from your druid
instance. See for more information the [Metadata](#metadata) chapter.

## QueryBuilder: Generic Query Methods

Here we will describe some methods which are generic and can be used by (almost) all queries.

#### `interval()`

Because Druid is a TimeSeries database, you always need to specify between which times you want to query. With this
method you can do just that.

The interval method is very flexible and supports various argument formats.

All these examples are valid:

```php
// Select an interval with string values. Anything which can be parsed by the DateTime object
// can be given. Also, "yesterday" or "now" is valid.
$builder->interval('2019-12-23', '2019-12-24');

// When a string is given which contains a slash, we will split it for you and parse it as "begin/end".
$builder->interval('yesterday/now');

// A "raw" interval as druid uses them is also allowed
$builder->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z');

// You can also give DateTime objects
$builder->interval(new DateTime('yesterday'), new DateTime('now'));

// Carbon is also supported, as it extends DateTime
$builder->interval(Carbon::now()->subDay(), Carbon::now());

// Timestamps are also supported:
$builder->interval(1570643085, 1570729485);
```

The start date should be before the end date. If not, an `InvalidArgumentException` will be thrown.

You can call this method multiple times to select from various data sets.

The `interval()` method has the following parameters:

| **Type**                  | **Optional/Required** | **Argument** | **Example**      | **Description**                                                                                                                                                                   |
|---------------------------|-----------------------|--------------|------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string/int/DateTime       | Required              | `$start`     | "now - 24 hours" | The start date from where we will query. See the examples above which formats are allowed.                                                                                        |
| /string/int/DateTime/null | Optional              | `$stop`      | "now"            | The stop date from where we will query. See the examples above which formats are allowed. When a string containing a slash is given as start date, the stop date can be left out. |

#### `limit()`

The `limit()` method allows you to limit the result set of the query.

The Limit can be used for all query types. However, its mandatory for the TopN Query and the Select Query.

The `$offset` parameter only applies to `GroupBy` and `Scan` queries and is only supported since druid version 0.20.0.

Skip this many rows when returning results. Skipped rows will still need to be generated internally and then discarded,
meaning that raising offsets to high values can cause queries to use additional resources.

Together, `$limit` and `$offset` can be used to implement pagination. However, note that if the underlying datasource is
modified in between page fetches in ways that affect overall query results, then the different pages will not
necessarily align with each other.

Example:

```
// Limit the result to 50 rows, but skipping the first 20 rows.
$builder->limit(50, 20);
```

The `limit()` method has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example** | **Description**                                   |
|----------|-----------------------|--------------|-------------|---------------------------------------------------|
| int      | Required              | `$limit`     | 50          | Limit the result to this given number of records. | 
| int      | Optional              | `$offset`    | 10          | Skip this many rows when returning results.       | 

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

| **Type** | **Optional/Required** | **Argument**         | **Example**              | **Description**                                                                                                        |
|----------|-----------------------|----------------------|--------------------------|------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimensionOrMetric` | "channel"                | The dimension or metric where you want to order by                                                                     |
| string   | Optional              | `$direction`         | `OrderByDirection::DESC` | The direction or your order. You can use an OrderByDirection constant, or a string like "asc" or "desc". Default "asc" |
| string   | Optional              | `$sortingOrder`      | `SortingOrder::STRLEN`   | This defines how the sorting is executed.                                                                              |

See for more information about SortingOrders this
page: https://druid.apache.org/docs/latest/querying/sorting-orders.html

Please note: this method differs per query type. Please read below how this method workers per Query Type.

**GroupBy Query**

You can call this method multiple times, adding an order-by to the query.
The GroupBy Query only allows ordering the result if there is a limit is given. If you do not supply a limit, we will
use
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

| **Type** | **Optional/Required** | **Argument** | **Example**              | **Description**                                                                                          |
|----------|-----------------------|--------------|--------------------------|----------------------------------------------------------------------------------------------------------|
| string   | Required              | `$direction` | `OrderByDirection::DESC` | The direction or your order. You can use an OrderByDirection constant, or a string like "asc" or "desc". |

#### `pagingIdentifier()`

The `pagingIdentifier()` allows you to do paginating on the result set. This only works on SELECT queries.

When you execute a select query, you will return a paging identifier. To request the next "page", use this paging
identifier in your next request.

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

A paging identifier is an array and looks something like this:

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
    ->selectVirtual("timestamp_format(__time, 'yyyy-MM-dd HH:00:00')", 'hour')
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
be confused with selecting dimensions for your other query types. See
[Dimension Selections](#querybuilder-dimension-selections) for more information about selecting dimensions for your
query.

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

The `toArray()` method will try to build the query. We will try to auto-detect the best query type. After that, we will
build the query and return the query as an array.

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

The `toJson()` method will try to build the query. We will try to auto-detect the best query type. After that, we will
build the query and return the query as a JSON string.

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

## QueryBuilder: Data Sources

By default, you will specify the dataSource where you want to select your data from with the
query. For example:

```php
$builder = $client->query('wikipedia');
```

In this chapter we will explain how to change it dynamically, or, for example, join other dataSources.

#### `from()`

You can use this method to override / change the currently used dataSource (if any).

You can supply a string, which will be interpreted as a druid dataSource table.
You can also specify an object which implements the `DataSourceInterface`.

This method has the following arguments:

| **Type**                   | **Optional/Required** | **Argument**  | **Example** | **Description**                                                   |
|----------------------------|-----------------------|---------------|-------------|-------------------------------------------------------------------|
| string/DataSourceInterface | Required              | `$dataSource` | hits        | The dataSource which you want to use to retrieve druid data from. |

```php
$builder = $client->query('hits_short');

// For example, use a different dataSource if the given date is older than one week.
if( Carbon::parse($date)->isBefore(Carbon::now()->subWeek()) ) {
    $builder->from('hits_long');
}
```

#### `fromInline()`

Inline datasources allow you to query a small amount of data that is embedded in the query itself.
They are useful when you want to write a query on a small amount of data without loading it first.
They are also useful as inputs into a join.

Each row is an array that must be exactly as long as the list of columnNames. The first element in
each row corresponds to the first column in columnNames, and so on.

See also: https://druid.apache.org/docs/latest/querying/datasource.html#inline

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument**   | **Example**                                                  | **Description**                                                                                                 |
|----------|-----------------------|----------------|--------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------|
| array    | Required              | `$columnNames` | ["country", "city"]                                          | The column names of the data you will supply.                                                                   |
| array    | Required              | `$rows`        | [ ["United States", "San Francisco"], ["Canada", "Calgary"]] | The rows of data which will be used. Each row has to have as much items as the number of names in $columnNames. |

```php
$builder = $client->query()->fromInline(
    ["country", "city"]
    [
        ["United States", "San Francisco"], 
        ["Canada", "Calgary"]
    ]
)->select(["country", "city"]); // etc. 
```

#### `join()`

With this method you can join another dataSource. This is available since druid version 0.23.0.

Please be aware that joins are executed as a subquery in Druid, which may have a substantial effect on the performance.

See:

- https://druid.apache.org/docs/latest/querying/datasource.html#join
- https://druid.apache.org/docs/latest/querying/query-execution.html#join

```php
$builder = $client->query('users')
    ->interval('now - 1 week', 'now')
    ->join('departments', 'dep', 'dep.id = users.department_id')
    ->select([ /*...*/ ]);
```

You can also specify a sub-query as a join. For example:

```php
$builder = $client->query('users')
    ->interval('now - 1 week', 'now')
    ->join(function(\Level23\Druid\Queries\QueryBuilder $subQuery) {
        $subQuery->from('departments')
            ->where('name', '!=', 'Staff');
    }, 'dep', 'dep.id = users.department_id')
    ->select([ /*...*/ ]);
```

You can also specify another DataSource as value. For example, you can create a new `JoinDataSource` object and pass
that as value. However, there are easy methods created for this (for example `joinLookup()`) so you probably
do not have to use this. It might be usefully for whe you want to join with inline data (you can use
the `InlineDataSource`)

This method has the following arguments:

| **Type**                           | **Optional/Required** | **Argument**           | **Example**      | **Description**                                                                                      |
|------------------------------------|-----------------------|------------------------|------------------|------------------------------------------------------------------------------------------------------|
| string/DataSourceInterface/Closure | Required              | `$dataSourceOrClosure` | countries        | The name of the dataSource which you want to join. You can also specify a Closure. Please see above. |
| string                             | Required              | `$as`                  | alias            | The alias name as this dataSource is accessible in your query.                                       |
| Closure                            | Required              | `$condition`           | alias.a = main.a | Here you can specify the condition of the join                                                       |
| string                             | Optional              | `$joinType`            | INNER            | The join type. This can either be INNER or LEFT.                                                     |

#### `leftJoin()`

This works the same as the `join()` method, but the joinType will always be LEFT.

#### `innerJoin()`

This works the same as the `join()` method, but the joinType will always be INNER.

#### `joinLookup()`

With this method you can join a lookup as a dataSource.

Lookup datasources are key-value oriented and always have exactly two columns: k (the key) and v (the value),
and both are always strings.

Example:

```php
$builder = $client->query('users')
    ->interval('now - 1 week', 'now')
    ->join('departments', 'dep', 'users.department_id = dep.k')
    ->select('dep.v', 'departmentName')
    ->select('...')
```

See: https://druid.apache.org/docs/latest/querying/datasource.html#lookup

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument**  | **Example**      | **Description**                                                |
|----------|-----------------------|---------------|------------------|----------------------------------------------------------------|
| string   | Required              | `$lookupName` | departments      | The name of the lookup which you want to join.                 |
| string   | Required              | `$as`         | alias            | The alias name as this dataSource is accessible in your query. |
| Closure  | Required              | `$condition`  | alias.a = main.a | Here you can specify the condition of the join                 |
| string   | Optional              | `$joinType`   | INNER            | The join type. This can either be INNER or LEFT.               |

#### `union()`

Unions allow you to treat two or more tables as a single datasource. In SQL, this is done with the UNION ALL operator
applied directly to tables, called a "table-level union". In native queries, this is done with a "union" datasource.

With the native union datasource, the tables being unioned do not need to have identical schemas.
If they do not fully match up, then columns that exist in one table but not another will be treated as
if they contained all null values in the tables where they do not exist.

In either case, features like expressions, column aliasing, JOIN, GROUP BY, ORDER BY, and so on cannot be
used with table unions.

See: https://druid.apache.org/docs/latest/querying/datasource.html#union

Example:

```php
$builder = $client->query('hits_us')
    ->union(['hits_eu', 'hits_as'], true);

// This will result in a query on the dataSources: hits_us, hits_eu and hits_as.
// This is because the "append" argument is set to true.

$builder = $client->query('hits_us')
    ->union(['hits_eu', 'hits_as'], false);

// This will result in a query on the dataSources: hits_eu and hits_as.
// This is because the "append" argument is set to false. It will overwrite the current dataSource.

```

This method has the following arguments:

| **Type**        | **Optional/Required** | **Argument**   | **Example**            | **Description**                                                                                                                                      |
|-----------------|-----------------------|----------------|------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------|
| string or array | Required              | `$dataSources` | ["hits_eu", "hits_us"] | The name of the dataSources which you want to query. NOTE! The current dataSource is automatically added!                                            |
| bool            | Optional              | `$append`      | true                   | This controls if the currently used dataSource should be added to this list or not. This only works if the current dataSource is a table dataSource. |

## QueryBuilder: Dimension Selections

Dimensions are fields where you normally filter on, or _Group_ data by. Typical examples are: Country, Name, City, etc.

To select a _dimension_, you can use one of the methods below:

#### `select()`

This method has the following arguments:

| **Type**        | **Optional/Required** | **Argument**      | **Example**                       | **Description**                                                                                                                                                                   |
|-----------------|-----------------------|-------------------|-----------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string or array | Required              | `$dimension`      | country_iso                       | The dimension which you want to select                                                                                                                                            |
| string          | Optional              | `$as`             | country                           | The name where the result will be available by in the result set.                                                                                                                 |
| string          | Optional              | `$outputType`     | string                            | The output type of the data. If left unspecified, we will use `string`.                                                                                                           |

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


**Change the output type of the dimension:**

```php 
$builder->select('age', null, DataType::LONG);
```

#### `lookup()`

This method allows you to look up a dimension using a registered lookup function. See more about registered lookup
functions on these pages:

* https://druid.apache.org/docs/latest/querying/lookups.html
* https://druid.apache.org/docs/latest/development/extensions-core/lookups-cached-global.html

Lookups are a handy way to transform an ID value into a user readable name, like transforming a `user_id` into the
`username`, without having to store the username in your dataset.

This method has the following arguments:

| **Type**    | **Optional/Required** | **Argument**        | **Example**    | **Description**                                                                                                                                                                                                                                                      |
|-------------|-----------------------|---------------------|----------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string      | Required              | `$lookupFunction`   | username_by_id | The name of the lookup function which you want to use for this dimension.                                                                                                                                                                                            |
| string      | Required              | `$dimension`        | user_id        | The dimension which you want to transform.                                                                                                                                                                                                                           |
| string      | Optional              | `$as`               | username       | The name where the result will be available by in the result set.                                                                                                                                                                                                    |
| bool/string | Optional              | `$keepMissingValue` | Unknown        | When the user_id dimension could not be found, what do you want to do? Use `false` for remove the value from the result, use `true` to keep the original dimension value (the user_id). Or, when a string is given, we will replace the value with the given string. |

Example:

```php
$builder->lookup('lookupUsername', 'user_id', 'username', 'Unknown'); 
```

#### `inlineLookup()`

This method allows you to look up a dimension using a predefined list.

Lookups are a handy way to transform an ID value into a user readable name, like transforming a `category_id` into the
`category`, without having to store the category in your dataset.

This method has the following arguments:

| **Type**    | **Optional/Required** | **Argument**        | **Example**                   | **Description**                                                                                                                                                                                                                                                      |
|-------------|-----------------------|---------------------|-------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| array       | Required              | `$map`              | `[1 => "IT", 2 => "Finance"]` | The list with key => value items, where the dimensions value will be used to find the value in the list.                                                                                                                                                             |
| string      | Required              | `$dimension`        | user_id                       | The dimension which you want to transform.                                                                                                                                                                                                                           |
| string      | Optional              | `$as`               | username                      | The name where the result will be available by in the result set.                                                                                                                                                                                                    |
| bool/string | Optional              | `$keepMissingValue` | Unknown                       | When the user_id dimension could not be found, what do you want to do? Use `false` for remove the value from the result, use `true` to keep the original dimension value (the user_id). Or, when a string is given, we will replace the value with the given string. |
| bool        | Optional              | `$isOneToOne`       | true                          | Set to true if the key/value items are unique in the given map.                                                                                                                                                                                                      |

Example:

```php

$departments = [
    1 => 'Administration',
    2 => 'Marketing',
    3 => 'Shipping',
    4 => 'IT',
    5 => 'Accounting',
    6 => 'Finance'
];

$builder->lookup($departments, 'department_id', 'department', 'Unknown'); 
```

#### `multiValueListSelect()`

This dimension spec retains only the values that are present in the given list.

See:

- https://druid.apache.org/docs/latest/querying/multi-value-dimensions.html
- https://druid.apache.org/docs/latest/querying/dimensionspecs.html#filtered-dimensionspecs

| **Type**        | **Optional/Required** | **Argument**  | **Example**      | **Description**                                                                  |
|-----------------|-----------------------|---------------|------------------|----------------------------------------------------------------------------------|
| string          | Required              | `$dimension`  | tags             | The name of the dimension which contains multiple values.                        |
| array           | Required              | `$values`     | ['a', 'b', 'c']  | Only use the values in the multi-value dimension which are present in this list. |
| string          | Optional              | `$as`         | myTags           | The name where the result will be available by in the result set.                |
| string/DataType | Optional              | `$outputType` | DataType::STRING | The data type of the dimension value.                                            |

Example:

```php
$builder->multiValueListSelect('tags', ['a', 'b', 'c'], 'testTags', DataType::STRING); 
```

#### `multiValueRegexSelect()`

This dimension spec retains only the values matching a regex.

See:

- https://druid.apache.org/docs/latest/querying/multi-value-dimensions.html
- https://druid.apache.org/docs/latest/querying/dimensionspecs.html#filtered-dimensionspecs

| **Type**        | **Optional/Required** | **Argument**  | **Example**      | **Description**                                                              |
|-----------------|-----------------------|---------------|------------------|------------------------------------------------------------------------------|
| string          | Required              | `$dimension`  | tags             | The name of the dimension which contains multiple values.                    |
| string          | Required              | `$regex`      | "^ab"            | The java regex pattern for the values which should be used in the resultset. |
| string          | Optional              | `$as`         | myTags           | The name where the result will be available by in the result set.            |
| string/DataType | Optional              | `$outputType` | DataType::STRING | The data type of the dimension value.                                        |

Example:

```php
$builder->multiValueRegexSelect('tags', '^test', 'testTags', DataType::STRING); 
```

#### `multiValuePrefixSelect()`

This dimension spec retains only the values matching the given prefix.

See:

- https://druid.apache.org/docs/latest/querying/multi-value-dimensions.html
- https://druid.apache.org/docs/latest/querying/dimensionspecs.html#filtered-dimensionspecs

| **Type**        | **Optional/Required** | **Argument**  | **Example**      | **Description**                                                   |
|-----------------|-----------------------|---------------|------------------|-------------------------------------------------------------------|
| string          | Required              | `$dimension`  | tags             | The name of the dimension which contains multiple values.         |
| string          | Required              | `$prefix`     | test             | Return all multi-value items which start with this given prefix.  |
| string          | Optional              | `$as`         | myTags           | The name where the result will be available by in the result set. |
| string/DataType | Optional              | `$outputType` | DataType::STRING | The data type of the dimension value.                             |

Example:

```php
$builder->multiValuePrefixSelect('tags', 'test', 'testTags', DataType::STRING); 
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

All the metrics aggregations do support a filter selection. If this is given, the metric aggregation will only be
applied to the records where the filters match.

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

**Note:** Alternatives are: `longSum()`, `doubleSum()` and `floatSum()`, which allow you to directly specify the output
type by using the appropriate method name. These methods do not have the `$type` parameter.

Example:

```php
$builder->sum('views', 'totalViews');
```

The `sum()` aggregation method has the following parameters:

| **Type**        | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                       |
|-----------------|-----------------------|------------------|----------------------------------------------|-----------------------------------------------------------------------------------------------------------------------|
| string          | Required              | `$metric`        | "views"                                      | The metric which you want to sum                                                                                      |
| string          | Optional              | `$as`            | "totalViews"                                 | The name which will be used in the output result                                                                      |
| string/DataType | Optional              | `$type`          | DataType::LONG                               | The output type of the sum. This can either be long, float or double. See also the DataType enum                      |
| Closure         | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only sum the records which match with the given filter. |

#### `min()`

The `min()` aggregation computes the minimum of all metric values.

**Note:** Alternatives are: `longMin()`, `doubleMin()` and `floatMin()`, which allow you to directly specify the output
type by using the appropriate method name. These methods do not have the `$type` parameter.

Example:

```php
$builder->min('age', 'minAge');
```

The `min()` aggregation method has the following parameters:

| **Type**        | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                                  |
|-----------------|-----------------------|------------------|----------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| string          | Required              | `$metric`        | "views"                                      | The metric which you want to calculate the minimum value of.                                                                                     |
| string          | Optional              | `$as`            | "totalViews"                                 | The name which will be used in the output result                                                                                                 |
| string/DataType | Optional              | `$type`          | DataType::LONG                               | The output type. This can either be long, float or double. See also the DataType enum                                                            |
| Closure         | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only calculate the minimum value of the records which match with the given filter. |

#### `max()`

The `max()` aggregation computes the maximum of all metric values.

**Note:** Alternatives are: `longMax()`, `doubleMax()` and `floatMax()`, which allow you to directly specify the output
type by using the appropriate method name. These methods do not have the `$type` parameter.

Example:

```php
$builder->max('age', 'maxAge');
```

The `max()` aggregation method has the following parameters:

| **Type**        | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                                  |
|-----------------|-----------------------|------------------|----------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| string          | Required              | `$metric`        | "views"                                      | The metric which you want to calculate the maximum value of.                                                                                     |
| string          | Optional              | `$as`            | "totalViews"                                 | The name which will be used in the output result                                                                                                 |
| string/DataType | Optional              | `$type`          | DataType::LONG                               | The output type. This can either be long, float or double. See also the DataType enum                                                            |
| Closure         | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only calculate the maximum value of the records which match with the given filter. |

#### `first()`

The `first()` aggregation computes the metric value with the minimum timestamp or 0 if no row exist.

**Note:** Alternatives are: `longFirst()`, `doubleFirst()`, `floatFirst()` and `stringFirst()`, which allow you to
directly specify the output type by using the appropriate method name. These methods do not have the `$type` parameter.

Example:

```php
$builder->first('device');
```

The `first()` aggregation method has the following parameters:

| **Type**        | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                              |
|-----------------|-----------------------|------------------|----------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| string          | Required              | `$metric`        | "device"                                     | The metric which you want to compute the first value of.                                                                                     |
| string          | Optional              | `$as`            | "firstDevice"                                | The name which will be used in the output result                                                                                             |
| string/DataType | Optional              | `$type`          | DataType::LONG                               | The output type. This can either be string, long, float or double. See also the DataType enum.                                               |
| Closure         | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only compute the first value of the records which match with the given filter. |

#### `last()`

The `last()` aggregation computes the metric value with the maximum timestamp or 0 if no row exist.

Note that queries with last aggregators on a segment created with rollup enabled will return the rolled up value,
and not the last value within the raw ingested data.

**Note:** Alternatives are: `longLast()`, `doubleLast()`, `floatLast()` and `stringLast()`, which allow you to
directly specify the output type by using the appropriate method name. These methods do not have the `$type` parameter.

Example:

```php
$builder->last('email');
```

The `last()` aggregation method has the following parameters:

| **Type**        | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                             |
|-----------------|-----------------------|------------------|----------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| string          | Required              | `$metric`        | "device"                                     | The metric which you want to compute the last value of.                                                                                     |
| string          | Optional              | `$as`            | "firstDevice"                                | The name which will be used in the output result                                                                                            |
| string/DataType | Optional              | `$type`          | DataType::LONG                               | The output type. This can either be string, long, float or double. See also the DataType enum.                                              |
| Closure         | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only compute the last value of the records which match with the given filter. |

#### `any()`

The `any()` aggregation will fetch any metric value. This can also be null.

**Note:** Alternatives are: `longAny()`, `doubleAny()`, `floatAny()` and `stringAny()`, which allow you to
directly specify the output type by using the appropriate method name. These methods do not have the `$type` parameter.

Example:

```php
$builder->any('price');
```

The `any()` aggregation method has the following parameters:

| **Type**        | **Optional/Required** | **Argument**      | **Example**                                  | **Description**                                                                                                                             |
|-----------------|-----------------------|-------------------|----------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| string          | Required              | `$metric`         | "device"                                     | The metric which you want to compute the last value of.                                                                                     |
| string          | Optional              | `$as`             | "anyDevice"                                  | The name which will be used in the output result                                                                                            |
| string/DataType | Optional              | `$type`           | DataType::STRING                             | The output type. This can either be string, long, float or double. See DataType enum                                                        |
| int             | Optional              | `$maxStringBytes` | 2048                                         | Then the type is string, you can specify here the max bytes of the string. Defaults to 1024.                                                |
| Closure         | Optional              | `$filterBuilder`  | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only compute the last value of the records which match with the given filter. |

#### `javascript()`

The `javascript()` aggregation computes an arbitrary JavaScript function over a set of columns (both metrics and
dimensions are allowed). Your JavaScript functions are expected to return floating-point values.

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

This page also explains the usage of hyperUnique very well:
https://cleanprogrammer.net/getting-unique-counts-from-druid-using-hyperloglog/

Example:

```php
$builder->hyperUnique('dimension', 'myResult');
```

The `hyperUnique()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**          | **Example** | **Description**                                                                                                                                                                                                      |
|----------|-----------------------|-----------------------|-------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$metric`             | "dimension" | The dimension that has been aggregated as a "hyperUnique" metric at indexing time.                                                                                                                                   |
| string   | Required              | `$as`                 | "myField"   | The name which will be used in the output result                                                                                                                                                                     |
| bool     | Optional              | `$round`              | true        | TheHyperLogLog algorithm generates decimal estimates with some error. "round" can be set to true to round off estimated values to whole numbers. Note that even with rounding, the cardinality is still an estimate. |
| bool     | Optional              | `$isInputHyperUnique` | false       | Only affects ingestion-time behavior, and is ignored at query-time. Set to true to index pre-computed HLL (Base64 encoded output from druid-hll is expected).                                                        |

#### `cardinality()`

The `cardinality()` aggregation computes the cardinality of a set of Apache Druid (incubating) dimensions,
using HyperLogLog to estimate the cardinality.

Please note: use `distinctCount()` when the Theta Sketch extension is available, as it is much faster.
This aggregator will also be much slower than indexing a column with the `hyperUnique()` aggregator.

In general, we strongly recommend using the `distinctCount()` or `hyperUnique()` aggregator instead of
the `cardinality()`
aggregator if you do not care about the individual values of a dimension.

When setting `$byRow` to `false` (the default) it computes the cardinality of the set composed of the union of al
dimension values for all the given dimensions. For a single dimension, this is equivalent to:

```sql
SELECT COUNT(DISTINCT (dimension))
FROM <datasource>
```

For multiple dimensions, this is equivalent to something akin to

```sql
SELECT COUNT(DISTINCT (value))
FROM (SELECT dim_1 as value
      FROM <datasource>
      UNION
      SELECT dim_2 as value
      FROM <datasource>
      UNION
      SELECT dim_3 as value
      FROM <datasource>)
```

When setting `$byRow` to `true` it computes the cardinality by row, i.e. the cardinality of distinct dimension
combinations. This is equivalent to something akin to

```sql
SELECT COUNT(*)
FROM (SELECT DIM1, DIM2, DIM3 FROM <datasource> GROUP BY DIM1, DIM2, DIM3)
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
    'itemsPerCountry',
    function(DimensionBuilder $dimensions) {
        // select the country name by its iso value.
        $dimensions->lookup('country_name', 'iso');        
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

This method uses the Theta Sketch extension, and it should be enabled to make use of this aggregator.  
For more information, see: https://druid.apache.org/docs/latest/development/extensions-core/datasketches-theta.html

Example:

```php
// Count the distinct number of categories. 
$builder->distinctCount('category_id', 'categoryCount');
```

The `distinctCount()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**     | **Example**                                  | **Description**                                                                                                                                                               |
|----------|-----------------------|------------------|----------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimension`     | "category_id"                                | The dimension where you want to count the distinct values from.                                                                                                               |
| string   | Optional              | `$as`            | "categoryCount"                              | The name which will be used in the output result                                                                                                                              |
| int      | Optional              | `$size`          | 16384                                        | Must be a power of 2. Internally, size refers to the maximum number of entries sketch object will retain. Higher size means higher accuracy but more space to store sketches. |
| Closure  | Optional              | `$filterBuilder` | See example in the beginning of this chapter | A closure which receives a FilterBuilder. When given, we will only count the records which match with the given filter.                                                       |

#### `doublesSketch()`

The `doublesSketch()` aggregation function will create a DoubleSketch data field which can be used by various
post aggregation methods to do extra calculations over the collected data.

DoubleSketch is a mergeable streaming algorithm to estimate the distribution of values, and approximately answer
queries about the rank of a value, probability mass function of the distribution (PMF) or histogram,
cumulative distribution function (CDF), and quantiles (median, min, max, 95th percentile and such).

This method uses the datasketches extension, and it should be enabled to make use of this aggregator.  
For more information, see: https://druid.apache.org/docs/latest/development/extensions-core/datasketches-quantiles.html

Example:

```php
// Get the 95th percentile of the salaries per country.
$builder = $client->query('dataSource')
    ->interval('now - 1 hour', 'now')
    ->select('country')
    ->doublesSketch('salary', 'salaryData') // this collects the data 
    ->quantile('quantile95', 'salaryData', 0.95) // this uses the data which was collected 
```

To view more information about the doubleSketch data, see the `sketchSummary()` post aggregation method.

The `doublesSketch()` aggregation method has the following parameters:

| **Type** | **Optional/Required** | **Argument**       | **Example**    | **Description**                                                                                                                                                                                                                                                                                                                                                                                                                                                |
|----------|-----------------------|--------------------|----------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$metric`          | `"salary"`     | The metric where you want to do calculations over.                                                                                                                                                                                                                                                                                                                                                                                                             |
| string   | Optional              | `$as`              | `"salaryData"` | The name which will be used in the output result.                                                                                                                                                                                                                                                                                                                                                                                                              |
| int      | Optional              | `$sizeAndAccuracy` | 128            | Parameter that determines the accuracy and size of the sketch. Higher k means higher accuracy but more space to store sketches. Must be a power of 2 from 2 to 32768. See accuracy information in the DataSketches documentation for details.                                                                                                                                                                                                                  |
| int      | Optional              | `$maxStreamLength` | 1000000000     | This parameter is a temporary solution to avoid a known issue. It may be removed in a future release after the bug is fixed. This parameter defines the maximum number of items to store in each sketch. If a sketch reaches the limit, the query can throw IllegalStateException. To workaround this issue, increase the maximum stream length. See accuracy information in the DataSketches documentation for how many bytes are required per stream length. |

## QueryBuilder: Filters

With filters, you can filter on certain values. The following filters are available:

#### `where()`

This is probably the most used filter. It is very flexible.

This method uses the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example**  | **Description**                                                                                                                                              |
|----------|-----------------------|--------------|--------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimension` | "cityName"   | The dimension which you want to filter.                                                                                                                      |
| string   | Required              | `$operator`  | "="          | The operator which you want to use to filter. See below for a complete list of supported operators.                                                          |
| mixed    | Optional              | `$value`     | "Auburn"     | The value which you want to use in your filter comparison. Set to null to match against NULL values.                                                         |
| string   | Optional              | `$boolean`   | "and" / "or" | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

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

#### `whereNot()`

With this filter you can build a filterset which should NOT match. It is thus inverted.

Example:

```php
$builder->whereNot(function (FilterBuilder $filterBuilder) {
    $filterBuilder->orWhere('namespace', 'Talk');
    $filterBuilder->orWhere('namespace', 'Main');
});
```

You can use this in combination with all the other filters!

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument**     | **Example** | **Description**                                                                                                                                              |
|----------|-----------------------|------------------|-------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Closure  | Required              | `$filterBuilder` | "flags"     | A closure function which will receive a `FilterBuilder` object. All applied filters will be inverted.                                                        |
| string   | Optional              | `$boolean`       | "and"       | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

#### `orWhereNot()`

Same as `whereNot()`, but now we will join previous added filters with a `or` instead of an `and`.

#### `whereNull()`

Druid has changed its NULL handling. You can now configure it to store `NULL` values by configuring
`druid.generic.useDefaultValueForNull=false`.

If this is configured, you can filter on NULL values with this filter.

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example** | **Description**                                                                                                                                              |
|----------|-----------------------|--------------|-------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$column`    | "city"      | The column or virtual column which you want to filter on null values.                                                                                        |
| string   | Optional              | `$boolean`   | "and"       | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

Example:

```php
// filter on all places where city name is NULL.
$builder->whereNull('city'); 

// filter on all places where the country is NOT NULL!
$builder->whereNot(function (FilterBuilder $filterBuilder) {
    $filterBuilder->whereNull('country');    
});
```

#### `orWhereNull()`

Same as `whereNull()`, but now we will join previous added filters with a `or` instead of an `and`.

#### `whereIn()`

With this method you can filter on records using multiple values.

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example**        | **Description**                                                                                                                                              |
|----------|-----------------------|--------------|--------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimension` | country_iso        | The dimension which you want to filter                                                                                                                       |
| array    | Required              | `$items`     | ["it", "de", "au"] | A list of values. We will return records where the dimension is in this list.                                                                                |
| string   | Optional              | `$boolean`   | "and"              | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

Example:

```php
// filter where country in "it", "de" or "au".
$builder->whereIn('country_iso', ['it', 'de', 'au']); 
```

#### `orWhereIn()`

Same as `whereIn()`, but now we will join previous added filters with a `or` instead of an `and`.

#### `whereArrayContains()`

With this method you can filter if an array contains a given element.

This method has the following arguments:

| **Type**              | **Optional/Required** | **Argument** | **Example** | **Description**                                                                                                                                              |
|-----------------------|-----------------------|--------------|-------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string                | Required              | `$column`    | country_iso | Input column or virtual column name to filter on.                                                                                                            |
| int/string/float/null | Required              | `$value`     | "it"        | Array element value to match. This value can be null.                                                                                                        |
| string                | Optional              | `$boolean`   | "and"       | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

Example:

```php
$builder->whereArrayContains('features', 'myNewFeature'); 
```


#### `orWhereArrayContains()`

Same as `whereArrayContains()`, but now we will join previous added filters with a `or` instead of an `and`.

#### `whereBetween()`

This filter will select records where the given dimension is greater than or equal to the given `$minValue`, and
less than the given `$maxValue`.

The SQL equivalent would be:
```SELECT ... WHERE field >= $minValue AND field < $maxValue```

This method has the following arguments:

| **Type**         | **Optional/Required** | **Argument** | **Example**       | **Description**                                                                                                                                              |
|------------------|-----------------------|--------------|-------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string           | Required              | `$dimension` | year              | The dimension which you want to filter                                                                                                                       |
| int/float/string | Required              | `$minValue`  | 1990              | The minimum value where the dimension should match. It should be equal or greater than this value.                                                           |
| int/float/string | Required              | `$maxValue`  | 2000              | The maximum value where the dimension should match. It should be less than this value.                                                                       |
| DataType         | Optional              | `$valueType` | `DataType::FLOAT` | This determines how druid will interprets the min and max values in comparison with the existing values. When not given we will auto detect it.              |
| string           | Optional              | `$boolean`   | "and"             | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

#### `orWhereBetween()`

Same as `whereBetween()`, but now we will join previous added filters with a `or` instead of an `and`.

#### `whereColumn()`

The `whereColumn()` filter compares two dimensions with each other. Only records where the dimensions match will be
returned.

The `whereColumn()` filter has the following arguments:

| **Type**       | **Optional/Required** | **Argument**  | **Example**  | **Description**                                                                                                                                              |
|----------------|-----------------------|---------------|--------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string/Closure | Required              | `$dimensionA` | "initials"   | The dimension which you want to compare, or a Closure which will receive a `DimensionBuilder` which allows you to select a dimension in a more advance way.  |
| string/Closure | Required              | `$dimensionB` | "first_name" | The dimension which you want to compare, or a Closure which will receive a `DimensionBuilder` which allows you to select a dimension in a more advance way.  |
| string         | Optional              | `$boolean`    | "and"        | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

#### `orWhereColumn()`

Same as `whereColumn()`, but now we will join previous added filters with a `or` instead of an `and`.

#### `whereInterval()`

The Interval filter enables range filtering on columns that contain long millisecond values, with the boundaries
specified as ISO 8601 time intervals. It is suitable for the __time column, long metric columns, and dimensions
with values that can be parsed as long milliseconds.

This filter converts the ISO 8601 intervals to long millisecond start/end ranges.
It will then use a between filter to see if the interval matches.

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example**       | **Description**                                                                                                                                              |
|----------|-----------------------|--------------|-------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimension` | __time            | The dimension which you want to filter                                                                                                                       |
| array    | Required              | `$intervals` | ['yesterday/now'] | See below for more info                                                                                                                                      |
| string   | Optional              | `$boolean`   | "and" / "or"      | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

The `$intervals` array can contain the following:

- an `Interval` object
- a raw interval string as used in druid. For example: "2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z"
- an interval string, separating the start and the stop with a / (for example "12-02-2019/13-02-2019")
- an array which contains 2 elements, a start and stop date. These can be an DateTime object, a unix timestamp or
  anything which can be parsed by DateTime::__construct

See for more info also the `interval()` method.

Example:

```php
$builder->whereInterval('__time', ['12-09-2019/13-09-2019', '19-09-2019/20-09-2019']);
```

#### `orWhereInterval()`

Same as `whereInterval()`, but now we will join previous added filters with a `or` instead of an `and`.

#### `whereFlags()`

This filter allows you to filter on a dimension where the value should match against your filter using a bitwise AND
comparison.

Support for 64-bit integers are supported.

Druid has support for bitwise flags since version 0.20.2. Before that, we have built our own variant, but then
javascript support is required. To make use of the javascript variant, you should pass `true` as the 4th parameter
of this method.

JavaScript-based functionality is disabled by default. Please refer to the Druid JavaScript programming guide for
guidelines about using Druid's JavaScript functionality, including instructions on how to enable it:
https://druid.apache.org/docs/latest/development/javascript.html

Example:

```php
$client = new \Level23\Druid\DruidClient([
    'router_url' => 'https://router.url:8080',
]);

$client->query('myDataSource')
    ->interval('now - 1 day', 'now')
    // Select records where the first and third bit are enabled (1 and 4)
    ->whereFlags('flags', (1 | 4));
```

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument**     | **Example** | **Description**                                                                                                                                              |
|----------|-----------------------|------------------|-------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimension`     | "flags"     | The dimension where you want to filter on                                                                                                                    |
| int      | Required              | `$flags`         | 64          | The flags which should match in the given dimension (comparing with a bitwise AND)                                                                           |
| string   | Optional              | `$boolean`       | "and"       | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |
| boolean  | Optional              | `$useJavascript` | true        | Older versions do not yet support the bitwiseAnd expression. Set this parameter to `true` to use an javacript alternative instead.                           |

#### `orWhereFlags()`

Same as `whereFlags()`, but now we will join previous added filters with a `or` instead of an `and`.

#### `whereExpression()`

This filter allows you to filter on a druid expression. See
also: https://druid.apache.org/docs/latest/querying/math-expr

This filter allows for more flexibility, but it might be less performant than a combination of the other filters on this
page due to the fact that not all filter optimizations are in place yet.

Example:

```php
$client = new \Level23\Druid\DruidClient([
    'router_url' => 'https://router.url:8080',
]);

$client->query('myDataSource')
    ->interval('now - 1 day', 'now')
    ->whereExpression('((product_type == 42) && (!is_deleted))');
```

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument**  | **Example**                                 | **Description**                                                                                                                                              |
|----------|-----------------------|---------------|---------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$expression` | `"((product_type == 42) && (!is_deleted))"` | The expression to use for your filter.                                                                                                                       |
| string   | Optional              | `$boolean`    | `"and"`                                     | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

#### `orWhereExpression()`

Same as `whereExpression()`, but now we will join previous added filters with a `or` instead of an `and`.

### `whereSpatialRectangular()`

This filter allows you to filter on your records where your spatial dimension is within the given rectangular shape.

Example:

```php
$client = new \Level23\Druid\DruidClient([
    'router_url' => 'https://router.url:8080',
]);

$client->query('myDataSource')
    ->interval('now - 1 day', 'now')
    ->whereSpatialRectangular('location', [0.350189, 51.248163], [-0.613861, 51.248163]);
```

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example**              | **Description**                                                                                                                                              |
|----------|-----------------------|--------------|--------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimension` | `"location"`             | The expression to use for your filter.                                                                                                                       |
| array    | Required              | `$minCoords` | `[0.350189, 51.248163]`  | List of minimum dimension coordinates for coordinates [x, y, z]                                                                                              |
| array    | Required              | `$maxCoords` | `[-0.613861, 51.248163]` | List of maximum dimension coordinates for coordinates [x, y, z]                                                                                              |
| string   | Optional              | `$boolean`   | "and"                    | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

### `orWhereSpatialRectangular()`

Same as `whereSpatialRectangular()`, but now we will join previous added filters with a `or` instead of an `and`.

### `whereSpatialRadius()`

This filter allows you to filter on your records where your spatial dimension is within radios of a given point.

Example:

```php
$client = new \Level23\Druid\DruidClient([
    'router_url' => 'https://router.url:8080',
]);

$client->query('myDataSource')
    ->interval('now - 1 day', 'now')
    ->whereSpatialRectangular('location', [0.350189, 51.248163], [-0.613861, 51.248163]);
```

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example**              | **Description**                                                                                                                                              |
|----------|-----------------------|--------------|--------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimension` | `"location"`             | The expression to use for your filter.                                                                                                                       |
| array    | Required              | `$minCoords` | `[0.350189, 51.248163]`  | List of minimum dimension coordinates for coordinates [x, y, z]                                                                                              |
| array    | Required              | `$maxCoords` | `[-0.613861, 51.248163]` | List of maximum dimension coordinates for coordinates [x, y, z]                                                                                              |
| string   | Optional              | `$boolean`   | `"and"`                  | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

### `orWhereSpatialRadius()`

Same as `whereSpatialRadius()`, but now we will join previous added filters with a `or` instead of an `and`.

### `whereSpatialPolygon()`

This filter allows you to filter on your records where your spatial dimension is within a given polygon.

Example:

```php
$client = new \Level23\Druid\DruidClient([
    'router_url' => 'https://router.url:8080',
]);

$client->query('myDataSource')
    ->interval('now - 1 day', 'now')
    ->whereSpatialPolygon('location', [0.350189, 51.248163], [-0.613861, 51.248163]);
```

This method has the following arguments:

| **Type** | **Optional/Required** | **Argument** | **Example**              | **Description**                                                                                                                                              |
|----------|-----------------------|--------------|--------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$dimension` | `"location"`             | The expression to use for your filter.                                                                                                                       |
| array    | Required              | `$abscissa`  | `[0.350189, 51.248163]`  | (The x axis) Horizontal coordinate for corners of the polygon                                                                                                |
| array    | Required              | `$ordinate`  | `[-0.613861, 51.248163]` | (The y axis) Vertical coordinate for corners of the polygon                                                                                                  |
| string   | Optional              | `$boolean`   | `"and"`                  | This influences how this filter will be joined with previous added filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

### `orWhereSpatialPolygon()`

Same as `orWhereSpatialPolygon()`, but now we will join previous added filters with a `or` instead of an `and`.

## QueryBuilder: Having Filters

With having filters, you can filter out records _after_ the data has been retrieved. This allows you to filter on
aggregated values.

See also this page: https://druid.apache.org/docs/latest/querying/having.html

Below are all the having methods explained.

#### `having()`

The `having()` filter is very similar to the `where()` filter. It is very flexible.

This method has the following arguments:

| **Type**   | **Optional/Required** | **Argument** | **Example**   | **Description**                                                                                                                                                            |
|------------|-----------------------|--------------|---------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string     | Required              | `$having`    | "totalClicks" | The metric which you want to filter.                                                                                                                                       |
| string     | Required              | `$operator`  | ">"           | The operator which you want to use to filter. See below for a complete list of supported operators.                                                                        |
| string/int | Required              | `$value`     | 50            | The value which you want to use in your filter comparison                                                                                                                  |
| string     | Optional              | `$boolean`   | "and" / "or"  | This influences how this having-filter will be joined with previous added having-filters. Should both filters apply ("and") or one or the other ("or") ? Default is "and". |

The following `$operator` values are supported:

| **Operator** | **Description**                                                                                                                                                 |
|--------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------|
| =            | Check if the metric is equal to the given value.                                                                                                                |
| !=           | Check if the metric is not equal to the given value.                                                                                                            |
| <>           | Same as `!=`                                                                                                                                                    |
| >            | Check if the metric is greater than the given value.                                                                                                            |
| >=           | Check if the metric is greater than or equal to the given value.                                                                                                |
| <            | Check if the metric is less than the given value.                                                                                                               |
| <=           | Check if the metric is less than or equal to the given value.                                                                                                   |
| like         | Check if the metric matches a SQL LIKE expression. Special characters supported are "%" (matches any number of characters) and "_" (matches any one character). |
| not like     | Same as `like`, only now the metric should not match.                                                                                                           |

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
// example using a having filter
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

For the full list of available expressions, see this page: https://druid.apache.org/docs/latest/querying/math-expr

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

| **Type**        | **Optional/Required** | **Argument**  | **Example**              | **Description**                                                                                                                              |
|-----------------|-----------------------|---------------|--------------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| string          | Required              | `$expression` | if( dimension > 0, 2, 1) | The expression which you want to use to create this virtual column.                                                                          |
| string          | Required              | `$as`         | "myVirtualColumn"        | The name of the virtual column created. You can use this name in a dimension (select it) or in an aggregation function.                      |
| string/DataType | Optional              | `$type`       | DataType::STRING         | The output type of this virtual column. Possible values are: string, float, long and double. Default is string.  See also the DataType enum. |

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

| **Type**        | **Optional/Required** | **Argument**  | **Example**              | **Description**                                                                                                                              |
|-----------------|-----------------------|---------------|--------------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| string          | Required              | `$expression` | if( dimension > 0, 2, 1) | The expression which you want to use to create this virtual column.                                                                          |
| string          | Required              | `$as`         | "myVirtualColumn"        | The name of the virtual column created. You can use this name in a dimension (select it) or in an aggregation function.                      |
| string/DataType | Optional              | `$type`       | DataType::STRING         | The output type of this virtual column. Possible values are: string, float, long and double. Default is string.  See also the DataType enum. |

## QueryBuilder: Post Aggregations

Post aggregations are aggregations which are executed after the result is fetched from the druid database.

#### `fieldAccess()`

The `fieldAccess()` post aggregator method is not really an aggregation method itself, but you need it to access fields
which are used
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

However, you can also use this shorthand, which will be converted to `fieldAccess` methods:

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

The `constant()` post aggregator method allows you to define a constant which can be used in a post aggregation
function.

For example, when you want to calculate the area of a circle based on the radius, you can use a formula like below:

Find the circle area based on the formula `(radius x radius x pi)`.

```php
$builder
    ->select('radius')
    ->multiply('area', function(PostAggregationsBuilder $builder){
        $builder->multiply('r2', ['radius', 'radius']);
        $builder->constant('3.141592654', 'pi');
    });
```

The `constant()` post aggregator has the following arguments:

| **Type**  | **Optional/Required** | **Argument**    | **Example** | **Description**                         |
|-----------|-----------------------|-----------------|-------------|-----------------------------------------|
| int/float | Required              | `$numericValue` | 3.14        | This will be our static value           |
| string    | Required              | `$as`           | pi          | The output name as how we can access it |

#### `expression()`

The `expression()` post aggregator method allows you to supply a Native Druid expression which allows you to compute a
result value.

Druid expressions allow you to do various actions, like:

* Execute a lookup and use the result
* Execute mathematical operations on values
* Use if, else statements
* Concat strings
* Use a "case" statement
* Etc.

For the full list of available expressions, see this page: https://druid.apache.org/docs/latest/querying/math-expr

Example:

```php
$builder
    ->sum('kids', 'totalKids')
    ->sum('adults', 'totalAdults')
    ->expression('totalHumans', 'totalKids + totalAdults', null, DataType::LONG)
```

The `expression()` post aggregator has the following arguments:

| **Type**        | **Optional/Required** | **Argument**  | **Example**     | **Description**                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
|-----------------|-----------------------|---------------|-----------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string          | Required              | `$as`         | pi              | The output name as how we can access it.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| string          | Required              | `$expression` | field1 + field2 | The expression which you want to compute.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| string          | Optional              | `$ordering`   | "numericFirst"  | If no ordering (or `null`) is specified, the "natural" ordering is used. `numericFirst` ordering always returns finite values first, followed by NaN, and infinite values last. If the expression produces array or complex types, specify ordering as null and use outputType instead to use the correct type native ordering.                                                                                                                                                                                                                                                                                                             |
| DataType/string | Optional              | `$outputType` | DOUBLE          | Output type is optional, and can be any native Druid type. Use a string value for ARRAY types (e.g. `ARRAY<LONG>`), or COMPLEX types (e.g. `COMPLEX<json>`). If not specified, the output type will be inferred from the expression. If specified and ordering is null, the type native ordering will be used for sorting values. If the expression produces array or complex types, this value must be non-null to ensure the correct ordering is used. If outputType does not match the actual output type of the expression, the value will be attempted to coerced to the specified type, possibly failing if coercion is not possible. |

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

The `hyperUniqueCardinality()` post aggregator is used to wrap a hyperUnique object such that it can be used in post
aggregations.

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

| **Type** | **Optional/Required** | **Argument**        | **Example** | **Description**                                                                    |
|----------|-----------------------|---------------------|-------------|------------------------------------------------------------------------------------|
| string   | Required              | `$hyperUniqueField` | myField     | The name of the hyperUnique field where you want to retrieve the cardinality from. |
| string   | Optional              | `$as`               | myResult    | The name which will be used in the output result.                                  |

#### `quantile()`

The `quantile()` post aggregator is used to return an approximation to the value that would be preceded by a
given fraction of a hypothetical sorted version of the input stream.

This method uses the Apache DataSketches library, and it should be enabled to make use of this post aggregator.  
For more information, see: https://druid.apache.org/docs/latest/development/extensions-core/datasketches-theta.html

Example:

```php
// Get the 95th percentile of the salaries per country.
$builder = $client->query('dataSource')
    ->interval('now - 1 hour', 'now')
    ->select('country')
    ->doublesSketch('salary', 'salaryData') // this collects the data 
    ->quantile('quantile95', 'salaryData', 0.95) // this uses the data which was collected 
```

The `quantile()` post aggregator has the following arguments:

| **Type**       | **Optional/Required** | **Argument**      | **Example** | **Description**                                                                                                                                                                                                                                                                                                                                                           |
|----------------|-----------------------|-------------------|-------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string         | Required              | `$as`             | myResult    | The name which will be used in the output result.                                                                                                                                                                                                                                                                                                                         |
| string/Closure | Required              | `$fieldOrClosure` | myField     | Field which will be used that refers to a DoublesSketch  (fieldAccess or another post aggregator). When a string is given, we assume that it refers to another field in the query. If you give a closure, it will receive an instance of the PostAggregationsBuilder. With this builder you can build another post-aggregation or use constants as input for this method. |
| float          | Required              | `$fraction`       | 0.95        | Fractional position in the hypothetical sorted stream, number from 0 to 1 inclusive                                                                                                                                                                                                                                                                                       |

#### `quantiles()`

The `quantiles()` post aggregator returns an array of quantiles corresponding to a given array of fractions.

This method uses the Apache DataSketches library, and it should be enabled to make use of this post aggregator.  
For more information, see: https://druid.apache.org/docs/latest/development/extensions-core/datasketches-theta.html

Example:

```php
// Get the 95th percentile of the salaries per country.
$builder = $client->query('dataSource')
    ->interval('now - 1 hour', 'now')
    ->select('country')
    ->doublesSketch('salary', 'salaryData') // this collects the data 
    ->quantiles('quantile95', 'salaryData', [0.8, 0.95]) // this uses the data which was collected 
```

The `quantiles()` post aggregator has the following arguments:

| **Type**       | **Optional/Required** | **Argument**      | **Example**   | **Description**                                                                                                                                                                                                                                                                                                                                                           |
|----------------|-----------------------|-------------------|---------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string         | Required              | `$as`             | myResult      | The name which will be used in the output result.                                                                                                                                                                                                                                                                                                                         |
| string/Closure | Required              | `$fieldOrClosure` | myField       | Field which will be used that refers to a DoublesSketch  (fieldAccess or another post aggregator). When a string is given, we assume that it refers to another field in the query. If you give a closure, it will receive an instance of the PostAggregationsBuilder. With this builder you can build another post-aggregation or use constants as input for this method. |
| array          | Required              | `$fraction`       | `[0.8, 0.95]` | Array of fractional positions in the hypothetical sorted stream, number from 0 to 1 inclusive                                                                                                                                                                                                                                                                             |

#### `histogram()`

The `histogram()` post aggregator returns an approximation to the histogram given an array of split points that define
the histogram bins or a number of bins (not both).
An array of m unique, monotonically increasing split points divide the real number line into m+1 consecutive disjoint
intervals.
The definition of an interval is inclusive of the left split point and exclusive of the right split point.
If the number of bins is specified instead of split points, the interval between the minimum and maximum values is
divided into the given number of equally-spaced bins.

This method uses the Apache DataSketches library, and it should be enabled to make use of this post aggregator.  
For more information, see: https://druid.apache.org/docs/latest/development/extensions-core/datasketches-theta.html

Example:

```php
// Create our builder
$builder = $client->query('dataSource')
    ->interval('now - 1 hour', 'now')
    ->select('country')
    ->doublesSketch('salary', 'salaryData') // this collects the data 
    // This would spit the data in "buckets". 
    // It will return an array with the number of people earning, 1000 or less, 
    // the number of people earning 1001 to 1500, etc.
    ->histogram('salaryGroups', 'salaryData', [1000, 1500, 2000, 2500, 3000, 3500, 4000, 4500, 5000, 5500]);  
```

The `histogram()` post aggregator has the following arguments:

| **Type**       | **Optional/Required** | **Argument**      | **Example**   | **Description**                                                                                                                                                                                                                                                                                                                                                           |
|----------------|-----------------------|-------------------|---------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string         | Required              | `$as`             | myResult      | The name which will be used in the output result.                                                                                                                                                                                                                                                                                                                         |
| string/Closure | Required              | `$fieldOrClosure` | myField       | Field which will be used that refers to a DoublesSketch  (fieldAccess or another post aggregator). When a string is given, we assume that it refers to another field in the query. If you give a closure, it will receive an instance of the PostAggregationsBuilder. With this builder you can build another post-aggregation or use constants as input for this method. |
| array          | Optional              | `$splitPoints`    | `[0.8, 0.95]` | An array of m unique, monotonically increasing split points divide the real number line into m+1 consecutive disjoint intervals.                                                                                                                                                                                                                                          | |
| int            | Optional              | `$numBins`        | `10`          | When no `$splitPoints` as defined, you can set the number of bins and the interval between the minimum and maximum values is divided into the given number of equally-spaced bins.                                                                                                                                                                                        |

The parameters `$splitPoints` and `$numBins` are mutually exclusive.

#### `rank()`

The `rank()` post aggregator returns an approximation to the rank of a given value that is the fraction
of the distribution less than that value.

This method uses the Apache DataSketches library, and it should be enabled to make use of this post aggregator.  
For more information, see: https://druid.apache.org/docs/latest/development/extensions-core/datasketches-theta.html

Example:

```php
// Create our builder
$builder = $client->query('dataSource')
    ->interval('now - 1 hour', 'now')
    ->select('country')
    ->doublesSketch('salary', 'salaryData') // this collects the data 
    // This will get the ranking of the value 2500 compared to all available "salary" values in the resultset.
    // The result will be a float between 0 and 1.
    ->rank('mySalaryRank', 'salaryData', 2500);  
```

The `rank()` post aggregator has the following arguments:

| **Type**       | **Optional/Required** | **Argument**      | **Example**   | **Description**                                                                                                                                                                                                                                                                                                                                                           |
|----------------|-----------------------|-------------------|---------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string         | Required              | `$as`             | myResult      | The name which will be used in the output result.                                                                                                                                                                                                                                                                                                                         |
| string/Closure | Required              | `$fieldOrClosure` | myField       | Field which will be used that refers to a DoublesSketch  (fieldAccess or another post aggregator). When a string is given, we assume that it refers to another field in the query. If you give a closure, it will receive an instance of the PostAggregationsBuilder. With this builder you can build another post-aggregation or use constants as input for this method. |
| array          | Optional              | `$splitPoints`    | `[0.8, 0.95]` | An array of m unique, monotonically increasing split points divide the real number line into m+1 consecutive disjoint intervals.                                                                                                                                                                                                                                          | |
| int            | Optional              | `$numBins`        | `10`          | When no `$splitPoints` as defined, you can set the number of bins and the interval between the minimum and maximum values is divided into the given number of equally-spaced bins.                                                                                                                                                                                        |

The parameters `$splitPoints` and `$numBins` are mutually exclusive.

#### `cdf()`

CDF stands for Cumulative Distribution Function.

The `cdf()` post aggregator returns an approximation to the Cumulative Distribution Function given an array of
split points that define the edges of the bins. An array of m unique, monotonically increasing split points divide
the real number line into m+1 consecutive disjoint intervals.
The definition of an interval is inclusive of the left split point and exclusive of the right split point.
The resulting array of fractions can be viewed as ranks of each split point with one additional rank that is always 1.

This method uses the Apache DataSketches library, and it should be enabled to make use of this post aggregator.  
For more information, see: https://druid.apache.org/docs/latest/development/extensions-core/datasketches-theta.html

Example:

```php
// Create our builder
$builder = $client->query('dataSource')
    ->interval('now - 1 hour', 'now')
    ->select('country')
    ->doublesSketch('salary', 'salaryData') // this collects the data 
    ->cdf('salaryGroups', 'salaryData', [1000, 1500, 2000, 2500, 3000, 3500, 4000, 4500, 5000, 5500]);
```

The `cdf()` post aggregator has the following arguments:

| **Type**       | **Optional/Required** | **Argument**      | **Example**   | **Description**                                                                                                                                                                                                                                                                                                                                                           |
|----------------|-----------------------|-------------------|---------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string         | Required              | `$as`             | myResult      | The name which will be used in the output result.                                                                                                                                                                                                                                                                                                                         |
| string/Closure | Required              | `$fieldOrClosure` | myField       | Field which will be used that refers to a DoublesSketch  (fieldAccess or another post aggregator). When a string is given, we assume that it refers to another field in the query. If you give a closure, it will receive an instance of the PostAggregationsBuilder. With this builder you can build another post-aggregation or use constants as input for this method. |
| array          | Optional              | `$splitPoints`    | `[0.8, 0.95]` | An array of m unique, monotonically increasing split points divide the real number line into m+1 consecutive disjoint intervals.                                                                                                                                                                                                                                          | |

#### `sketchSummary()`

CDF stands for Cumulative Distribution Function.

The `sketchSummary()` post aggregator returns a summary of the sketch that can be used for debugging.
This is the result of calling toString() method.

This method uses the Apache DataSketches library, and it should be enabled to make use of this post aggregator.  
For more information, see: https://druid.apache.org/docs/latest/development/extensions-core/datasketches-theta.html

Example:

```php
// Create our builder
$builder = $client->query('dataSource')
    ->interval('now - 1 hour', 'now')
    ->select('country')
    ->doublesSketch('salary', 'salaryData') // this collects the data 
    ->sketchSummary('debug', 'salaryData');
```

The `sketchSummary()` post aggregator has the following arguments:

| **Type**       | **Optional/Required** | **Argument**      | **Example** | **Description**                                                                                                                                                                                                                                                                                                                                                          |
|----------------|-----------------------|-------------------|-------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string         | Required              | `$as`             | myResult    | The name which will be used in the output result.                                                                                                                                                                                                                                                                                                                        |
| string/Closure | Required              | `$fieldOrClosure` | myField     | Field which will be used that refers to a DoublesSketch (fieldAccess or another post aggregator). When a string is given, we assume that it refers to another field in the query. If you give a closure, it will receive an instance of the PostAggregationsBuilder. With this builder you can build another post-aggregation or use constants as input for this method. |

Example output:

```
### Quantiles HeapUpdateDoublesSketch SUMMARY: 
   Empty                        : false
   Direct, Capacity bytes       : false, 
   Estimation Mode              : true
   K                            : 128
   N                            : 28,025
   Levels (Needed, Total, Valid): 7, 7, 5
   Level Bit Pattern            : 1101101
   BaseBufferCount              : 121
   Combined Buffer Capacity     : 1,152
   Retained Items               : 761
   Compact Storage Bytes        : 6,120
   Updatable Storage Bytes      : 9,248
   Normalized Rank Error        : 1.406%
   Normalized Rank Error (PMF)  : 1.711%
   Min Value                    : 0.000000e+00
   Max Value                    : 8.000000e-03
### END SKETCH SUMMARY
```

## QueryBuilder: Search Filters

Search filters are filters which are only used for a search query. They allow you to specify which filter should be
applied
to the given dimensions.

There are a few different filters available:

#### `searchContains()`

The `searchContains()` method allows you to filter on dimensions where the dimension contains your given value. You can
specify if the match should be case-sensitive or not.

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

The `searchFragment()` method allows you to filter on dimensions where the dimension contains ALL the given string
values. You can specify if the match should be case-sensitive or not.

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

See this page for more information about regular
expressions: https://docs.oracle.com/javase/6/docs/api/java/util/regex/Pattern.html

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

| **Type** | **Optional/Required** | **Argument** | **Example** | **Description**                                                |
|----------|-----------------------|--------------|-------------|----------------------------------------------------------------|
| string   | Required              | `$pattern`   | "^Wiki"     | A regular expression where the dimension should match against. |

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

The response of this method is dependent of the query which is executed. Each query has its own response object.
However, all query responses are extended of the `QueryResponse` object. Each query response has therefor
a `$response->raw()` method which will return an array with the raw data returned by druid. There is also
an `$response->data()` method which returns the data in a "normalized" way so that it can be directly used.

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

The `groupBy()` method has the following arguments:

| **Type**           | **Optional/Required** | **Argument** | **Example**        | **Description**                                           |
|--------------------|-----------------------|--------------|--------------------|-----------------------------------------------------------|
| array/QueryContext | Optional              | `$context`   | ['priority' => 75] | Query context parameters. See below for more information. |

**Context**

The `groupBy()` method accepts 1 parameter, the query context. This can be given as an array with key => value pairs,
or an `GroupByQueryContext` object.

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
$context = new GroupByQueryContext();
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
$response = $client->query('wikipedia', Granularity::ALL)
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

The `topN()` method receives 1 parameter, the query context. The query context is either an array with key => value
pairs, or an `TopNQueryContext` object. The context allows you to change the behaviour of the query execution.

Example:

```php
$builder = $client->query('wikipedia', Granularity::ALL)
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

The `selectQuery()` method will execute your query as a select query. It's important to not mix up this method with the
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

// ... Use your response (page 1) here! ...

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

The `selectQuery()` method receives 1 parameter, the query context. The query context is either an array with key =>
value pairs, or an `QueryContext` object. There is no SelectQueryContext, as there are no context parameters specific
for this query type. The context allows you to change the behaviour of the query execution.

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
The `$response->data()` method returns the data as an array in a "normalized" way so that it can be directly used. <br>
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

The first parameter of the `scan()` method is the query context. The query context is either an array with key => value
pairs, or an `ScanQueryContext` object. The context allows you to change the behaviour of the query execution.

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
The `$response->data()` method returns the data as an array in a "normalized" way so that it can be directly used.

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

For more information about the TimeSeries query, see this
page: https://druid.apache.org/docs/latest/querying/timeseriesquery.html

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

The `timeseries()` method receives 1 parameter, the query context. The query context is either an array with key =>
value pairs, or an `TimeSeriesQueryContext` object.
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
The `$response->data()` method returns the data as an array in a "normalized" way so that it can be directly used.

#### `search()`

The `search()` method executes your query as a Search Query. A Search Query will return the unique values of a dimension
which matches a specific search selection. The response will be containing the dimension which matched your search
criteria, the value of your dimension and the number of occurrences.

For more information about the Search Query, see this
page: https://druid.apache.org/docs/latest/querying/searchquery.html

See the [Search Filters](#querybuilder-search-filters) for examples how to specify your search filter.

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

| **Type**            | **Optional/Required** | **Argument**    | **Example**            | **Description**                                           |
|---------------------|-----------------------|-----------------|------------------------|-----------------------------------------------------------|
| array/QueryContext  | Optional              | `$context`      | ['priority' => 75]     | Query context parameters. See below for more information. |
| string/SortingOrder | Optional              | `$sortingOrder` | `SortingOrder::STRLEN` | This defines how the sorting is executed.                 |

**Context**

The `search()` method receives as first parameter the query context. The query context is either an array with key =>
value pairs, or an `QueryContext` object. The context allows you to change the behaviour of the query execution.

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
The `$response->data()` method returns the data as an array in a "normalized" way so that it can be directly used.

## Metadata

Besides querying data, the `DruidClient` class also allows you to extract metadata from your druid setup.

The `metadata()` method returns a `MetadataBuilder` instance. With this instance you can retrieve various metadata
information about your druid setup.

Below we have described the most common used methods.

#### `metadata()->intervals()`

The `intervals()` method returns all intervals for the given `$dataSource`.

Example:

```php
$intervals = $client->metadata()->intervals('wikipedia');
```

The `intervals()` method has 1 parameter:

| **Type** | **Optional/Required** | **Argument**  | **Example** | **Description**                                                                   |
|----------|-----------------------|---------------|-------------|-----------------------------------------------------------------------------------|
| string   | Required              | `$dataSource` | "wikipedia" | The name of the dataSource (table) which you want to retrieve the intervals from. |

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

#### `metadata()->timeBoundary()`

The `timeBoundary()` method returns the time boundary for the given dataSource.
It finds the first and/or last occurrence of a record in the given dataSource.

Optionally, you can also apply a filter. For example, to only see when the first and/or last occurrence
was for a record where a specific condition was met.

The return type varies per given $bound. If TimeBound::BOTH was given (or null, which is the same),
we will return an array with the minTime and maxTime:

```
array(
 'minTime' => \DateTime object,
 'maxTime' => \DateTime object
)
```

If only one time was requested with either TimeBound::MIN_TIME or TimeBound::MAX_TIME, we will return
a DateTime object.

The `timeBoundary()` method has the following parameters:

| **Type**                   | **Optional/Required** | **Argument**     | **Example**         | **Description**                                                                                                                                                          |
|----------------------------|-----------------------|------------------|---------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string/DataSourceInterface | Required              | `$dataSource`    | "wikipedia"         | The name of the dataSource (table) where you want the boundary from. This can also be a DataSource object.                                                               |
| null/ string/TimeBound     | Optional              | `$bound`         | TimeBound::BOTH     | Set to TimeBound::MAX_TIME or TimeBound::MIN_TIME ( or "maxTime"/"minTime") to return only the latest or earliest timestamp. Default to returning both if not set (null) |
| Closure                    | Optional              | `$filterBuilder` | See below           | A closure which receives a FilterBuilder. When given, we will get the bound(s) for the records which match with the given filter.                                        |
| Context                    | Optional              | `$context`       | ['timeout' => 1000] | Query context parameters.                                                                                                                                                |

Example:

```php
// Example of only retrieving the MAX time
$response = $client->metadata()->timeBoundary('wikipedia', TimeBound::MAX_TIME, function(FilterBuilder $builder) {
    $builder->where('channel', '!=', '#vi.wikipedia');
});

echo $response->format('d-m-Y H:i:s');

// Example of only retrieving BOTH times
$response = $client->metadata()->timeBoundary('wikipedia', TimeBound::BOTH);

echo $response['minTime']->format('d-m-Y H:i:s') .' / '. $response['maxTime']->format('d-m-Y H:i:s');
``` 

#### `metadata()->dataSources()`

This method will return all dataSources as an array.

Example:

```php
// Retrieve all data sources
$dataSources = $client->metadata()->dataSources();

foreach($dataSources as $dataSource) { 
    // ...
}
```

#### `metadata()->rowCount()`

Retrieve the number of rows for the given dataSource and interval.

The `rowCount()` method has the following parameters:

| **Type**                  | **Optional/Required** | **Argument**  | **Example**      | **Description**                                                                                                                                                                           |
|---------------------------|-----------------------|---------------|------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string                    | Required              | `$dataSource` | "wikipedia"      | The name of the dataSource (table) where you want to count the rows for.                                                                                                                  |
| string/int/DateTime       | Required              | `$start`      | "now - 24 hours" | The start date to retrieve the row count for. See [interval()](#interval) for all allowed formats.                                                                                        |
| /string/int/DateTime/null | Optional              | `$stop`       | "now"            | The stop date to retrieve the row count for. See [interval()](#interval) for all allowed formats. When a string containing a slash is given as start date, the stop date can be left out. |

Example:

```php
// Retrieve the total records for the past week.
$numRows = $client->metadata()->rowCount("wikipedia", "now - 1 week", "now");
```

## Reindex / compact data / kill

Druid stores data in segments. When you want to update some data, you have to rebuild the _whole_ segment.
Therefore, we use smaller segments when the data is still "fresh".
In our experience, if data needs to be updated (rebuild), it is most of the time fresh data.
By keeping fresh data in smaller segments, we only need to rebuild 1 hour of data, instead for a whole month or such.

We use for example hour segments for "today" and "yesterday", and we have some processes which will change this data
into bigger segments after that.

Reindexing and compacting data is therefor very important to us. Here we show you how you can use this.

**Note**: when you re-index data, druid will collect the data and put it in a new segment. The old segments are not
deleted, but marked as unused. This is the same principle as Laravel soft-deletes. To permanently delete the unused
segments you should use the `kill` task. See below for an example.

By default, we have added a check to make sure that you have selected a complete interval. This prevents a lot of
issues. If you do _not_ want this, we have added a special context setting named `skipIntervalValidation`. When you set
this to `true`, we will not validate the given intervals for the `compact()` or `reindex()` methods.

Example:

```php
// Build our compact task.
$taskId = $client->compact('wikipedia')
    ->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z ')
    ->segmentGranularity(Granularity::DAY) 
    ->execute([ 'skipIntervalValidation' => true ]); // Ignore interval validation. 
```

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

// Or, simply use:
// $status = $client->pollTaskStatus($taskId);

echo "Final status: \n";
print_r($status->data());
```

The `compact` method will return a `CompactTaskBuilder` object which allows you to specify the rest of the
required data.

**NOTE:** We currently do not have support for building metricSpec and DimensionSpec yet.

#### `reindex()`

With the `reindex()` method you can re-index data which is already in a druid dataSource. You can do a bit more than
with the `compact()` method.

For example, you can filter or transform existing data or change the query granularity:

```php
$client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

// Create our custom input source.
$source = new DruidInputSource('wikipedia');
$source->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z');
$source->where('namespace', 'not like', '%Draft%');

// Build our reindex task
$taskId = $client->reindex('wikipedia-new')
    ->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z ')
    ->parallel()
    // Here we overwrite our "source" data, we define our own source data.
    ->inputSource($source) 
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

// Or, simply use:
// $status = $client->pollTaskStatus($taskId);

echo "Final status: \n";
print_r($status->data());
```

The `reindex` method will return a `IndexTaskBuilder` object which allows you to specify the rest of the
required data. By default, we will use a `DruidInputSource` to ingest data from an existing data source.

If you want you can change the data source where the data is read from using the `inputSource()` method.
See the [Input Sources](#input-sources) chapter for other input sources.

#### `kill()`

The `kill()` method will return a `KillTaskBuilder` object. This allows you to specify the interval and optionally
the task ID for your task. You can then execute it.

The kill task will delete all __unused__ segments which match with your given interval. If you often re-index your data
you probably want to also use this task a lot, otherwise you will also store all old versions of your data.

If you want to remove segments which are not yet marked as __unused__, you can use the `markAsUnused()` method:

Example:

```php
$client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

// Build our kill task and execute it.
$taskId = $client->kill('wikipedia')
    ->interval('2015-09-12T00:00:00.000Z/2015-09-13T00:00:00.000Z ')
    ->markAsUnused() // mark segments as unused
    ->execute();

echo "Kill task inserted with id: " . $taskId . "\n";

// Start polling task status.
while (true) {
    $status = $client->taskStatus($taskId);
    echo $status->getId() . ': ' . $status->getStatus() . "\n";

    if ($status->getStatus() != 'RUNNING') {
        break;
    }
    sleep(2);
}

// Or, simply use:
// $status = $client->pollTaskStatus($taskId);

echo "Final status: \n";
print_r($status->data());
```

## Importing data using a batch index job

When you want to manually import data into druid, you can do this with a simple `index` task.
When you want to import data, you will have to specify an input source. The input source is where the data is read from.

There are various input sources, for example a Local file, an HTTP endpoint or data retrieved from an SQL source.
Below we will describe all available input sources, but first we will explain how an index task is created.

The `$client->index(...)` method returns an `IndexTaskBuilder` object, which allows you to specify your index task.

It is important to understand that druid will replace your SEGMENTS by default!
So, for example, of you stored your data in DAY segments, then you have to import your data for that whole segment in
one task. Otherwise, the second task will replace the previous data.

To solve this, you can use `appendToExisting()`, which will allow you to append to an existing segment without removing
the previous imported data.

For more methods on the `IndexTaskBuilder`, see the example below. Above each method call we have added some comment as
explanation:

```php
$client = new DruidClient(['router_url' => 'http://127.0.0.1:8888']);

// First, define your inputSource. 
$inputSource = new \Level23\Druid\InputSources\HttpInputSource([
    'https://your-site.com/path/to/file1.json',
    'https://your-site.com/path/to/file2.json',
]);

# Now, build and execute our index task
$taskId = $client->index('myTableName', $inputSource)
    // specify the date range which will be imported.
    ->interval('now - 1 week', 'now')
    // Specify that we want to "rollup" our data 
    ->rollup()
    // We want to make segment files of 1 week of data
    ->segmentGranularity(Granularity::WEEK)
    // We want to be able to query at minimum level of HOUR data.
    ->queryGranularity(Granularity::HOUR)
    // Process the input source parallel (like multithreaded instead of 1 thread).
    ->parallel()
    // By default, an INDEX task will OVERWRITE _segments_. If you want to APPEND, use this: 
    ->appendToExisting()    
    // Set a unique id for this task.
    ->taskId('MY-TASK')
    // Specify your "time" column in your input source
    ->timestamp('time', 'posix')
    // Now we will add some dimensions which we want to add to our data-source.
    // These are the field names to read from input records, as well as the column name stored in generated segments.
    ->dimension('country', 'string')
    ->dimension('age', 'long')
    ->dimension('version', 'float')
    // You can also import spatial dimensions (x,y(,z)) coordinates
    ->spatialDimension('location', ['lat', 'long'])
    // Import multi-value dimensions
    ->multiValueDimension('tags', 'string')
    // Add the metrics which we want to ingest from our input source. (only when rollup is enabled!)
    ->sum('clicks', 'totalClicks', 'long')
    ->sum('visits', 'totalVisits', 'long')
    ->sum('revenue', 'profit', 'float')
    // Execute the task
    ->execute();
    
// If you want to stop your task (for whatever reason), you can call:    
// $client->cancelQuery($taskId);    
    
// Now poll for our final status    
$status = $client->pollTaskStatus($taskId);

echo "Final status: \n";
print_r($status->data());       
```

## Input Sources

To index data, you need to specify where the data is read from. You can do this with an

#### `AzureInputSource`

The AzureInputSource reads data from your Azure Blob store or Azure Data Lake sources.

Important! You need to include the `druid-azure-extensions` as an extension to use the Azure input source.

The constructor allows you to specify the following parameters:

| **Type** | **Optional/Required** | **Argument** | **Example**                                                                                                         | **Description**                                                                                                                        |
|----------|-----------------------|--------------|---------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| array    | Optional              | `$uris`      | `["azure://<container>/<path-to-file>", ...]`                                                                       | Array of URIs where the Azure objects to be ingested are located.                                                                      |
| array    | Optional              | `$prefixes`  | `["azure://<container>/<prefix>", ...]`                                                                             | Array of URI prefixes for the locations of Azure objects to ingest. Empty objects starting with one of the given prefixes are skipped. |
| array    | Optional              | `$objects`   | `[ ["bucket" => "container", "path" => "path/file1.json"], ["bucket" => "container", "path" => "path/file2.json"]]` | Array of Azure objects to ingest.                                                                                                      |

Either one of these parameters is required. When you execute your index task in parallel, each task will process one (or
more)
of the objects given.

Example:

```php

// First, define your inputSource. 
$inputSource = new \Level23\Druid\InputSources\AzureInputSource([
    'azure://bucket/file1.json',
    'azure://bucket/file2.json',
]);

# Now, start building your task (import it into a datasource called azureData) 
$indexTaskBuilder = $client->index('azureData', $inputSource);
// $indexTaskBuilder-> ...
```

#### `GoogleCloudInputSource`

The GoogleCloudInputSource reads data from your Azure Blob store or Azure Data Lake sources.

Important! You need to include the `druid-google-extensions` as an extension to use the Google Cloud Storage input
source.

The constructor allows you to specify the following parameters:

| **Type** | **Optional/Required** | **Argument** | **Example**                                                                                                         | **Description**                                                                                                                               |
|----------|-----------------------|--------------|---------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------|
| array    | Optional              | `$uris`      | `["gs://<container>/<path-to-file>", ...]`                                                                          | Array of URIs where the Google Cloud Storage to be ingested are located.                                                                      |
| array    | Optional              | `$prefixes`  | `["gs://<container>/<prefix>", ...]`                                                                                | Array of URI prefixes for the locations of Google Cloud Storage to ingest. Empty objects starting with one of the given prefixes are skipped. |
| array    | Optional              | `$objects`   | `[ ["bucket" => "container", "path" => "path/file1.json"], ["bucket" => "container", "path" => "path/file2.json"]]` | Array of Google Cloud Storage to ingest.                                                                                                      |

Either one of these parameters is required. When you execute your index task in parallel, each task will process one (or
more)
of the objects given.

Example:

```php

// First, define your inputSource. 
$inputSource = new \Level23\Druid\InputSources\GoogleCloudInputSource([
    'gs://bucket/file1.json',
    'gs://bucket/file2.json',
]);

# Now, start building your task (import it into a datasource called googleData) 
$indexTaskBuilder = $client->index('googleData', $inputSource);
// $indexTaskBuilder-> ...
```

#### `S3InputSource`

The S3InputSource reads data from Amazon S3.

Important! You need to include the `druid-s3-extensions` as an extension to use the S3 input source.

The constructor allows you to specify the following parameters:

| **Type** | **Optional/Required** | **Argument**  | **Example**                                                                                                         | **Description**                                                                                                                              |
|----------|-----------------------|---------------|---------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| array    | Optional              | `$uris`       | `["s3://<bucket>/<path-to-file>", ...]`                                                                             | Array of URIs where S3 objects to be ingested are located.                                                                                   |
| array    | Optional              | `$prefixes`   | `["s3://<bucket>/<prefix>", ...]`                                                                                   | Array of URI prefixes for the locations of S3 objects to be ingested. Empty objects starting with one of the given prefixes will be skipped. |
| array    | Optional              | `$objects`    | `[ ["bucket" => "container", "path" => "path/file1.json"], ["bucket" => "container", "path" => "path/file2.json"]]` | Array of S3 Objects to be ingested.                                                                                                          |
| array    | Optional              | `$properties` | `["accessKeyId" => "KLJ78979SDFdS2", ... ]`                                                                         | Properties array for overriding the default S3 configuration. See below for more information.                                                |

Either one of these parameters is required. When you execute your index task in parallel, each task will process one (or
more)
of the objects given.

Example:

```php

// First, define your inputSource. 
$inputSource = new \Level23\Druid\InputSources\S3InputSource(
    [
        's3://bucket/file1.json',
        's3://bucket/file2.json',
    ],
    [], // no prefixes
    [], // no objects
    [
        "accessKeyId" => "KLJ78979SDFdS2", 
        "secretAccessKey" => "KLS89s98sKJHKJKJH8721lljkd", 
        "assumeRoleArn" => "arn:aws:iam::2981002874992:role/role-s3",
    ]
);

# Now, start building your task (import it into a datasource called awsS3Data) 
$indexTaskBuilder = $client->index('awsS3Data', $inputSource);
// $indexTaskBuilder-> ...
```

#### `HdfsInputSource`

The HdfsInputSource reads files directly from HDFS storage.

Important! You need to include the `druid-hdfs-storage` as an extension to use the HDFS input source.

The constructor allows you to specify the following parameters:

| **Type** | **Optional/Required** | **Argument** | **Example**                                                                             | **Description**                                                                                                                                                                             |
|----------|-----------------------|--------------|-----------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| array    | Required              | `$paths`     | `["hdfs://namenode_host/foo/bar/file.json", "hdfs://namenode_host/bar/foo/file2.json"]` | HDFS paths. Can be either a JSON array or comma-separated string of paths. Wildcards like * are supported in these paths. Empty files located under one of the given paths will be skipped. |

When you execute your index task in parallel, each task will process one (or more)
of the files given.

Example:

```php

// First, define your inputSource. 
$inputSource = new \Level23\Druid\InputSources\HdfsInputSource(
    ["hdfs://namenode_host/foo/bar/file.json", "hdfs://namenode_host/bar/foo/file2.json"]
);

# Now, start building your task (import it into a datasource called hdfsData) 
$indexTaskBuilder = $client->index('hdfsData', $inputSource);
// $indexTaskBuilder-> ...
```

#### `HttpInputSource`

The HttpInputSource reads files directly from remote sites via HTTP.

The constructor allows you to specify the following parameters:

| **Type**     | **Optional/Required** | **Argument** | **Example**                                               | **Description**                                                                                                                                          |
|--------------|-----------------------|--------------|-----------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------|
| array        | Required              | `$uris`      | `["http://example.com/uri1", "http://example2.com/uri2"]` | URIs of the input files.                                                                                                                                 |
| string       | Optional              | `$username`  | `"john"`                                                  | Username to use for authentication with specified URIs. Can be optionally used if the URIs specified in the spec require a Basic Authentication Header.  |
| string/array | Optional              | `$password`  | `"isTheBest"`                                             | Password or PasswordProvider to use with specified URIs. Can be optionally used if the URIs specified in the spec require a Basic Authentication Header. |

When you execute your index task in parallel, each task will process one (or more)
of the files (uris) given.

Example:

```php
// First, define your inputSource. 

// Example 1. Without Basic Authentication 
$inputSource = new \Level23\Druid\InputSources\HttpInputSource(
    ["http://example.com/uri1", "http://example2.com/uri2"]
);

// Example 2. In this example we have a plain username-password combination. 
$inputSource = new \Level23\Druid\InputSources\HttpInputSource(
    ["http://example.com/uri1", "http://example2.com/uri2"],
    "username",
    "password"
);

// Example 3. In this example we use the password provider. 
$inputSource = new \Level23\Druid\InputSources\HttpInputSource(
    ["http://example.com/uri1", "http://example2.com/uri2"],
    "username",
    [
        "type" => "environment",
        "variable" => "HTTP_INPUT_SOURCE_PW"
    ]
);

# Now, start building your task (import it into a datasource called httpData) 
$indexTaskBuilder = $client->index('httpData', $inputSource);
// $indexTaskBuilder-> ...
```

#### `InlineInputSource`

The InlineInputSource reads the data directly from what is given.
It can be used for demos or for quickly testing out parsing and schema.

The constructor allows you to specify the following parameters:

| **Type** | **Optional/Required** | **Argument** | **Example**                                     | **Description**                         |
|----------|-----------------------|--------------|-------------------------------------------------|-----------------------------------------|
| array    | Required              | `$data`      | `[["row1", 16, 9.18], ["row2", 12, 9.22], ...]` | Array with rows which contain the data. |

Example:

```php
// First, define your inputSource. 
$inputSource = new \Level23\Druid\InputSources\InlineInputSource([
    ["row1", 16, 9.18], 
    ["row2", 12, 9.22],
    // ...
]);

# Now, start building your task (import it into a datasource called inlineData) 
$indexTaskBuilder = $client->index('inlineData', $inputSource);
// $indexTaskBuilder-> ...
```

#### `LocalInputSource`

The LocalInputSource reads files directly from local storage.

The constructor allows you to specify the following parameters:

| **Type** | **Optional/Required**     | **Argument** | **Example**                | **Description**                                                                                                                                                  |
|----------|---------------------------|--------------|----------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| array    | Required without $baseDir | `$files`     | `["/bar/foo", "/foo/bar"]` | File paths to ingest. Some files can be ignored to avoid ingesting duplicate files if they are located under the specified baseDir. Empty files will be skipped. |
| string   | Required without $files   | `$baseDir`   | `"/data/directory"`        | Directory to search recursively for files to be ingested. Empty files under the baseDir will be skipped.                                                         |
| string   | Required with $baseDir    | `$filter`    | `"*.csv"`                  | A wildcard filter for files.                                                                                                                                     |

Example:

```php
// First, define your inputSource. 

// Example 1, specify the files to ingest
$inputSource = new \Level23\Druid\InputSources\LocalInputSource([
    ["/bar/foo/file.json", "/foo/bar/file.json"]
]);

// Example 2, specify a dir and wildcard for files to ingest
$inputSource = new \Level23\Druid\InputSources\LocalInputSource([
    [],
    "/path/to/dir",
    "*.json"
]);

# Now, start building your task (import it into a datasource called inlineData) 
$indexTaskBuilder = $client->index('inlineData', $inputSource);
// $indexTaskBuilder-> ...
```

#### `DruidInputSource`

The DruidInputSource reads data directly from existing druid segments.

The constructor allows you to specify the following parameters:

| **Type**          | **Optional/Required** | **Argument**  | **Example**                          | **Description**                                                                                                        |
|-------------------|-----------------------|---------------|--------------------------------------|------------------------------------------------------------------------------------------------------------------------|
| array             | Required              | `$dataSource` | `["/bar/foo", "/foo/bar"]`           | The datasource where you want to read data from.                                                                       |
| IntervalInterface | Optional              | `$inteval`    | `new Interval('now - 1 day', 'now')` | The interval which will be used for reading data from your datasource. Only records within this interval will be read. |
| FilterInterface   | Optional              | `$filter`     | (See below)                          | A filter which will be used to select records which will be read. Only records matching this filter will be used.      |

Example:

```php
// First, define your inputSource. 

// Example 1, specify the files to ingest
$inputSource = new \Level23\Druid\InputSources\DruidInputSource('hits');

// only process records from a week ago until now.
$inputSource->interval('now - 1 week', 'now');

// only process records matching these filters.
$inputSource->where('browser', 'Android');
$inputSource->whereIn('version', ['8', '9', '10']);
// etc.

# Now, start building your task (import it into a datasource called androidHits) 
$indexTaskBuilder = $client->index('androidHits', $inputSource);
// $indexTaskBuilder-> ...
```

#### `SqlInputSource`

The SqlInputSource reads records directly from a database using queries which you will specify.
In parallel mode, each task will process one or more queries.

Note: If you want to use mysql as source, you must have enabled the extension `mysql-metadata-storage` in druid.
If you want to use postgresql as source, you must have enabled the extension `postgresql-metadata-storage` in druid.

Since this input source has a fixed input format for reading events, no inputFormat field needs to be specified in the
ingestion spec when using this input source. Please refer to the Recommended practices section below before using this
input source.

See https://druid.apache.org/docs/latest/ingestion/native-batch.html#sql-input-source for more information.

The constructor allows you to specify the following parameters:

| **Type** | **Optional/Required** | **Argument**  | **Example**                                                                        | **Description**                                                                                                                                                          |
|----------|-----------------------|---------------|------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| string   | Required              | `$connectURI` | `"jdbc:mysql://host:port/schema"`                                                  | The connection URI to connect with your database.                                                                                                                        |
| string   | Required              | `$username`   | `"user"`                                                                           | The username used for authentication.                                                                                                                                    |
| string   | Required              | `$password`   | `"password"`                                                                       | The password used for authentication.                                                                                                                                    |
| array    | Required              | `$sqls`       | `["select * from table where type = 'a'", "select * from table where type = 'b'"]` | A list of queries which will be executed to retrieve the data which you want to import.                                                                                  |
| boolean  | Optional              | `$foldCase`   | `true`                                                                             | Toggle case folding of database column names. This may be enabled in cases where the database returns case insensitive column names in query results. Default is `false` |

Example:

```php
// First, define your inputSource. 

// Example 1, specify the files to ingest
$inputSource = new \Level23\Druid\InputSources\SqlInputSource(
    "jdbc:mysql://host:port/schema",
    "username",
    "password",
    [
        "select * from table where type = 'a'", 
        "select * from table where type = 'b'"
    ]
);
# Now, start building your task (import it into a datasource called mysqlData) 
$indexTaskBuilder = $client->index('mysqlData', $inputSource);
// $indexTaskBuilder-> ...
```

#### `CombiningInputSource`

The CombiningInputSource allows you to retrieve data from multiple locations. It combines various input source methods.

This input source should be only used if all the delegate input sources are splittable and can be used by the Parallel
task.
This input source will identify the splits from its delegates and each split will be processed by a worker task.
Similar to other input sources, this input source supports a single inputFormat. Therefore, please note that delegate
input sources requiring an inputFormat must have the same format for input data.

The constructor allows you to specify the following parameters:

| **Type** | **Optional/Required** | **Argument**    | **Example**                                          | **Description**                                                        |
|----------|-----------------------|-----------------|------------------------------------------------------|------------------------------------------------------------------------|
| array    | Required              | `$inputSources` | `[new HttpInputSource(...), new S3InputSource(...)]` | List with other import sources which should be processed all together. |

Example:

```php
// First, define your inputSource. 

// Example 1, specify the files to ingest
$inputSource = new \Level23\Druid\InputSources\CombiningInputSource([
    new \Level23\Druid\InputSources\HttpInputSource(['http://127.0.0.1/file.json']),
    new \Level23\Druid\InputSources\S3InputSource(['s3://bucket/file2.json'])
]);

# Now, start building your task (import it into a datasource called combinedData) 
$indexTaskBuilder = $client->index('combinedData', $inputSource);
// $indexTaskBuilder-> ...
```

## Input Formats

For most input sources you also need to specify the format of the incoming data. You can do this with an input format.
You can choose several input formats in your TaskBuilder. Below they are explained.

## `csvFormat()`

The `csvFormat()` allows you to specify how your csv data is build.

This method allows you to specify the following parameters:

| **Type** | **Optional/Required** | **Argument**             | **Example**       | **Description**                                                                                           |
|----------|-----------------------|--------------------------|-------------------|-----------------------------------------------------------------------------------------------------------|
| array    | Required              | `$columns`               | `["name", "age"]` | Specifies the columns of the data. The columns should be in the same order with the columns of your data. |
| string   | Optional              | `$listDelimiter`         | `"$"`             | A custom delimiter for multi-value dimensions.                                                            |
| boolean  | Optional              | `$findColumnsFromHeader` | `true`            | If this is set, the task will find the column names from the header row.                                  |
| int      | Optional              | `$skipHeaderRows`        | `2`               | If this is set, the task will skip the first skipHeaderRows rows.                                         |

Note that skipHeaderRows will be applied before finding column names from the header. For example, if you set
skipHeaderRows to 2 and findColumnsFromHeader to true, the task will skip the first two lines and then extract column
information from the third line.

Example:

```php
$inputSource = new HttpInputSource( /*...*/ );

$builder = $client->index('data', $inputSource)
    ->csvFormat(['name', 'age'], null, true, 2)
    //-> ....
;
```

## `tsvFormat()`

The `tsvFormat()` allows you to specify how your tsv data is build.

This method allows you to specify the following parameters:

| **Type** | **Optional/Required** | **Argument**             | **Example**       | **Description**                                                                                           |
|----------|-----------------------|--------------------------|-------------------|-----------------------------------------------------------------------------------------------------------|
| array    | Required              | `$columns`               | `["name", "age"]` | Specifies the columns of the data. The columns should be in the same order with the columns of your data. |
| string   | Optional              | `$delimiter`             | `"\t"`            | A custom delimiter for data values (default is a tab `\t`).                                               |
| string   | Optional              | `$listDelimiter`         | `"$"`             | A custom delimiter for multi-value dimensions.                                                            |
| boolean  | Optional              | `$findColumnsFromHeader` | `true`            | If this is set, the task will find the column names from the header row.                                  |
| int      | Optional              | `$skipHeaderRows`        | `2`               | If this is set, the task will skip the first skipHeaderRows rows.                                         |

Be sure to change the delimiter to the appropriate delimiter for your data. Like CSV, you must specify the columns
and which subset of the columns you want indexed.

Note that skipHeaderRows will be applied before finding column names from the header. For example, if you set
skipHeaderRows to 2 and findColumnsFromHeader to true, the task will skip the first two lines and then extract column
information from the third line.

Example:

```php
$inputSource = new HttpInputSource( /*...*/ );

$builder = $client->index('data', $inputSource)
    ->tsvFormat(['name', 'age'], "|", null, true, 2)
    //-> ....
;
```

## `jsonFormat()`

The `jsonFormat()` allows you to specify how the data is formatted.

See also:

- https://github.com/FasterXML/jackson-core/wiki/JsonParser-Features
- https://druid.apache.org/docs/latest/ingestion/data-formats.html#flattenspec

This method allows you to specify the following parameters:

| **Type**    | **Optional/Required** | **Argument**   | **Example** | **Description**                                                                   |
|-------------|-----------------------|----------------|-------------|-----------------------------------------------------------------------------------|
| FlattenSpec | Optional              | `$flattenSpec` | (see below) | Specifies flattening configuration for nested JSON data. See below for more info. |
| array       | Optional              | `$features`    | `"\t"`      | List the features which apply for this json input format.                         |

The flattenSpec object bridges the gap between potentially nested input data, such as JSON or Avro, and Druid's flat
data model.
It is an object within the inputFormat object.

```php
$inputSource = new HttpInputSource( /*...*/ );

// Here we define how our fields are "read" from the input source. 
$spec = new FlattenSpec(true);
$spec->field(FlattenFieldType::ROOT, 'baz');
$spec->field(FlattenFieldType::JQ, 'foo_bar', '$.foo.bar');
$spec->field(FlattenFieldType::PATH, 'first_food', '.thing.food[1]');

$builder = $client->index('data', $inputSource)
    ->jsonFormat($spec, ['ALLOW_SINGLE_QUOTES' => true, 'ALLOW_UNQUOTED_FIELD_NAMES' => true])
    //-> ....
;
```

## `orcFormat()`

The `orcFormat()` allows you to specify the ORC input format. However, to make use of this input source, you should have
added the `druid-orc-extensions` to druid.

See:

- https://druid.apache.org/docs/latest/development/extensions-core/orc.html
- https://druid.apache.org/docs/latest/ingestion/data-formats.html#flattenspec

This method allows you to specify the following parameters:

| **Type**    | **Optional/Required** | **Argument**      | **Example** | **Description**                                                                                                                             |
|-------------|-----------------------|-------------------|-------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| FlattenSpec | Optional              | `$flattenSpec`    | (see below) | Specifies flattening configuration for nested JSON data. See below for more info.                                                           |
| boolean     | Optional              | `$binaryAsString` | `true`      | Specifies if the binary orc column which is not logically marked as a string should be treated as a UTF-8 encoded string. Default is false. |

The flattenSpec object bridges the gap between potentially nested input data, and Druid's flat data model.
It is an object within the inputFormat object.

```php
$inputSource = new HttpInputSource( /*...*/ );

// Here we define how our fields are "read" from the input source. 
$spec = new FlattenSpec(true);
$spec->field(FlattenFieldType::ROOT, 'baz');
$spec->field(FlattenFieldType::JQ, 'foo_bar', '$.foo.bar');
$spec->field(FlattenFieldType::PATH, 'first_food', '.thing.food[1]');

$builder = $client->index('data', $inputSource)
    ->orcFormat($spec, true)
    //-> ....
;
```

## `parquetFormat()`

The `parquetFormat()` allows you to specify the Parquet input format. However, to make use of this input source, you
should have
added the `druid-parquet-extensions` to druid.

See:

- https://druid.apache.org/docs/latest/development/extensions-core/parquet.html
- https://druid.apache.org/docs/latest/ingestion/data-formats.html#flattenspec

This method allows you to specify the following parameters:

| **Type**    | **Optional/Required** | **Argument**      | **Example** | **Description**                                                                                                                                             |
|-------------|-----------------------|-------------------|-------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------|
| FlattenSpec | Optional              | `$flattenSpec`    | (see below) | Specifies flattening configuration for nested JSON data. See below for more info.                                                                           |
| boolean     | Optional              | `$binaryAsString` | `true`      | Specifies if the bytes parquet column which is not logically marked as a string or enum type should be treated as a UTF-8 encoded string. Default is false. |

The flattenSpec object bridges the gap between potentially nested input data, and Druid's flat data model.
It is an object within the inputFormat object.

```php
$inputSource = new HttpInputSource( /*...*/ );

// Here we define how our fields are "read" from the input source. 
$spec = new FlattenSpec(true);
$spec->field(FlattenFieldType::ROOT, 'baz');
$spec->field(FlattenFieldType::PATH, 'nested', '$.path.to.nested');

$builder = $client->index('data', $inputSource)
    ->parquetFormat($spec, true)
    //-> ....
;
```

## `protobufFormat()`

The `parquetFormat()` allows you to specify the Protobuf input format. However, to make use of this input source, you
should have
added the `druid-protobuf-extensions` to druid.

See:

- https://druid.apache.org/docs/latest/development/extensions-core/protobuf.html
- https://druid.apache.org/docs/latest/ingestion/data-formats.html#flattenspec

This method allows you to specify the following parameters:

| **Type**    | **Optional/Required** | **Argument**         | **Example** | **Description**                                                                   |
|-------------|-----------------------|----------------------|-------------|-----------------------------------------------------------------------------------|
| array       | Optional              | `$protoBytesDecoder` | (see below) | Specifies how to decode bytes to Protobuf record.                                 |
| FlattenSpec | Optional              | `$flattenSpec`       | (see below) | Specifies flattening configuration for nested JSON data. See below for more info. |

The flattenSpec object bridges the gap between potentially nested input data, and Druid's flat data model.
It is an object within the inputFormat object.

```php
$inputSource = new HttpInputSource( /*...*/ );

// Here we define how our fields are "read" from the input source. 
$spec = new FlattenSpec(true);
$spec->field(FlattenFieldType::ROOT, 'baz');
$spec->field(FlattenFieldType::PATH, 'someRecord_subInt', '$.someRecord.subInt');

$builder = $client->index('data', $inputSource)
    ->protobufFormat([
        "type" => "file",
        "descriptor" => "file:///tmp/metrics.desc",
        "protoMessageType" => "Metrics"
    ], $spec)
    //-> ....
;
```
