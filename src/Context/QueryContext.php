<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

use Level23\Druid\Arrayable;
use InvalidArgumentException;

/**
 * Class QueryContext
 *
 * The query context is used for various query configuration parameters. The following parameters apply to all queries.
 *
 * @package Level23\Druid\Context
 */
class QueryContext implements ContextInterface, Arrayable
{
    /**
     * GroupByQueryContext constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        foreach ($properties as $key => $value) {

            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException(
                    'Setting ' . $key . ' was not found in the ' . __CLASS__ . ' query context'
                );
            }

            if (!is_scalar($value)) {
                throw new InvalidArgumentException(
                    'Invalid value ' . var_export($value, true) .
                    ' for ' . $key . ' for the groupBy query context'
                );
            }

            $this->$key = $value;
        }
    }

    /**
     * Query timeout in millis, beyond which unfinished queries will be cancelled. 0 timeout means no timeout. To set
     * the default timeout, see Broker configuration
     *
     * @var int
     */
    public $timeout;

    /**
     * Query Priority. Queries with higher priority get precedence for computational resources.
     *
     * @var int
     */
    public $priority;

    /**
     * Unique identifier given to this query. If a query ID is set or known, this can be used to cancel the query
     *
     * @var string
     */
    public $queryId;

    /**
     * Flag indicating whether to leverage the query cache for this query. When set to false, it disables reading from
     * the query cache for this query. When set to true, Apache Druid (incubating) uses druid.broker.cache.useCache or
     * druid.historical.cache.useCache to determine whether or not to read from the query cache
     *
     * @var bool
     */
    public $useCache;

    /**
     * Flag indicating whether to save the results of the query to the query cache. Primarily used for debugging. When
     * set to false, it disables saving the results of this query to the query cache. When set to true, Druid uses
     * druid.broker.cache.populateCache or druid.historical.cache.populateCache to determine whether or not to save the
     * results of this query to the query cache
     *
     * @var bool
     */
    public $populateCache;

    /**
     * Flag indicating whether to leverage the result level cache for this query. When set to false, it disables
     * reading from the query cache for this query. When set to true, Druid uses druid.broker.cache.useResultLevelCache
     * to determine whether or not to read from the result-level query cache
     *
     * @var bool
     */
    public $useResultLevelCache;

    /**
     * Flag indicating whether to save the results of the query to the result level cache. Primarily used for
     * debugging. When set to false, it disables saving the results of this query to the query cache. When set to true,
     * Druid uses druid.broker.cache.populateResultLevelCache to determine whether or not to save the results of this
     * query to the result-level query cache
     *
     * @var bool
     */
    public $populateResultLevelCache;

    /**
     * Return "by segment" results. Primarily used for debugging, setting it to true returns results associated with
     * the data segment they came from
     *
     * @var bool
     */
    public $bySegment;

    /**
     * Flag indicating whether to "finalize" aggregation results. Primarily used for debugging. For instance, the
     * hyperUnique aggregator will return the full HyperLogLog sketch instead of the estimated cardinality when this
     * flag is set to false
     *
     * @var bool
     */
    public $finalize;

    /**
     * At the Broker process level, long interval queries (of any type) may be broken into shorter interval queries to
     * parallelize merging more than normal. Broken up queries will use a larger share of cluster resources, but, if
     * you use groupBy "v1, it may be able to complete faster as a result. Use ISO 8601 periods. For example, if this
     * property is set to P1M (one month), then a query covering a year would be broken into 12 smaller queries. The
     * broker uses its query processing executor service to initiate processing for query chunks, so make sure
     * "druid.processing.numThreads" is configured appropriately on the broker. groupBy queries do not support
     * chunkPeriod by default, although they do if using the legacy "v1" engine. This context is deprecated since it's
     * only useful for groupBy "v1", and will be removed in the future releases.
     *
     * @var string
     */
    public $chunkPeriod;

    /**
     * Maximum number of bytes gathered from data processes such as Historicals and realtime processes to execute a
     * query. This parameter can be used to further reduce maxScatterGatherBytes limit at query time. See Broker
     * configuration for more details.
     *
     * @var int
     */
    public $maxScatterGatherBytes;

    /**
     * Maximum number of bytes queued per query before exerting backpressure on the channel to the data server. Similar
     * to maxScatterGatherBytes, except unlike that configuration, this one will trigger backpressure rather than query
     * failure. Zero means disabled.
     *
     * @var int
     */
    public $maxQueuedBytes;

    /**
     * If true, DateTime is serialized as long in the result returned by Broker and the data transportation between
     * Broker and compute process
     *
     * @var bool
     */
    public $serializeDateTimeAsLong;

    /**
     * If true, DateTime is serialized as long in the data transportation between Broker and compute process
     *
     * @var bool
     */
    public $serializeDateTimeAsLongInner;

    /**
     * Return the context as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        $properties = [
            'timeout',
            'priority',
            'queryId',
            'useCache',
            'populateCache',
            'useResultLevelCache',
            'populateResultLevelCache',
            'bySegment',
            'finalize',
            'chunkPeriod',
            'maxScatterGatherBytes',
            'maxQueuedBytes',
            'serializeDateTimeAsLong',
            'serializeDateTimeAsLongInner',
        ];

        foreach ($properties as $property) {
            if ($this->$property !== null) {
                $result[$property] = $this->$property;
            }
        }

        return $result;
    }
}