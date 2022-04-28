## Changelog

**v1.1.1**

- OrderBy now defaults to `asc` direction
- `limit()` now supports an offset. See [limit()](#limit)
- Added `version` configuration option, which lets the query builder know which version of druid you are running.
- `whereFlags()` now uses native bitwise and operator if it is supported by your used version.
- Added more query context and TuningConfig properties. We now also allow unknown properties to be set, in case of a new
  value has been added.
- Updated CompactTask to use the ioConfig syntax as described in the manual.
- Removed deprecated IngestSegmentFirehose, now use DruidInputSource.
- Updated IndexTask (Native batch ingestion) to correct syntax as described in the manual.
- Added support for PHP 8.

**v1.2**

- Added support for DataSketches aggregator `doublesSketch()`
- Added DataSketches post aggregators:
    - `quantile()`
    - `quantiles()`
    - `histogram()`
    - `rank()`
    - `cdf()`
    - `sketchSummary()`

**v2.0**

- Updated minimal supported PHP version to 7.4, which allows us to use property type hinting, short function syntax and more.
- Removed DRUID_VERSION hinting. This was only used for `whereFlags()` and made our code ugly. You can now use the oldskool
  javascript variant by setting the new 4th parameter to true.
- Added `markAsUnused` option to kill task and the KillTaskBuilder.
- Refactored the IndexTask. It now allows an InputSource. Also added option to specify timestamp column. Added support
  to be able to ingest spatial dimensions using the `spatialDimension()` method in the IndexTaskBuilder.
- Added spatial filter methods:
    - `whereSpatialRectangular()`
    - `whereSpatialRadius()`
    - `whereSpatialPolygon()`
    - `orWhereSpatialRectangular()`
    - `orWhereSpatialRadius()`
    - `orWhereSpatialPolygon()`
- Added ExpressionFilter and the `whereExpression()` and `orWhereExpression()` methods.
- Removed IngestSegmentFirehose and FirehoseInterface. These are now replaced by InputSources.
- Added _a lot_ of [input sources](#input-sources) (used for index tasks):
    - Azure
    - Google Cloud
    - S3
    - HDFS
    - Http
    - Local
    - Inline
    - SQL
    - Combine
- Added [input formats](#input-formats) (used for index tasks):
    - csv Format
    - tsv Format
    - json Format
    - orc Format
    - parquet Format
    - protobuf Format
- Updated DruidInputSource so that you now can also specify a filter on it.
- Added `pollTaskStatus` in the client, which will poll until the status of a task is other than `RUNNING`.
  The time between each check can be influenced by the `'polling_sleep_seconds'` config setting or
  the `DRUID_POLLING_SLEEP_SECONDS` .env setting for Laravel/Lumen applications.
- Added support to join other DataSources.

## Migrating to v2

If you are currently using druid-client version 1.*, you should check for these breaking code changes:

1. The `IndexTaskBuilder` constructor now only accepts an InputSourceInterface as second parameter.
2. The `IndexTaskBuilder` has no `fromDataSource()` and `setFromDataSource()` methods anymore. These where related to the
   IngestSegmentFirehose.
3. IngestSegmentFirehose and FirehoseInterface are gone. You should now use the InputSource variant instead.
4. We removed DRUID_VERSION and 'version' from the config. This was only used for the `whereFlags()` methods. If you want
   to fall back to the old javascript behaviour, you can now use the 4th parameter `$useJavascript`. If you do not use
   the javascript variant, no changes are required.
5. You can remove the `'version'` settings from your config, as the `DRUID_VERSION` from your .env if you are using this.
   However, if you do not remove them it will not break.
6. Removed deprecated `getPagingIdentifier()` from SelectQueryResponse class.
7. All Query Types (`GroupByQuery`, `SelectQuery`, etc) now receive a `DataSourceInterface` object instead of a string
   as dataSource. 
8. The protected method `QueryBuilder::buildQuery()` is renamed to `QueryBuilder::getQuery()` and is now public. 
