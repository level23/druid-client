<?php
declare(strict_types=1);

namespace Level23\Druid\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Druid
 *
 * @method static \Level23\Druid\Queries\QueryBuilder query(string $dataSource = '', string $granularity = \Level23\Druid\Types\Granularity::ALL)
 * @method static void cancelQuery(string $identifier)
 * @method static array executeQuery(\Level23\Druid\Queries\QueryInterface $druidQuery)
 * @method static string executeTask(\Level23\Druid\Tasks\TaskInterface $task)
 * @method static array executeRawRequest(string $method, string $url, array $data = [])
 * @method static \Level23\Druid\DruidClient setLogger(\Psr\Log\LoggerInterface $logger)
 * @method static \Level23\Druid\DruidClient setGuzzleClient(\GuzzleHttp\Client $client)
 * @method static mixed config(string $key, $default = null)
 * @method static \Level23\Druid\Metadata\MetadataBuilder metadata()
 * @method static \Level23\Druid\Responses\TaskResponse taskStatus(string $taskId)
 * @method static \Level23\Druid\Responses\TaskResponse pollTaskStatus(string $taskId)
 * @method static \Level23\Druid\Tasks\CompactTaskBuilder compact(string $dataSource)
 * @method static \Level23\Druid\Tasks\KillTaskBuilder kill(string $dataSource)
 * @method static \Level23\Druid\Tasks\IndexTaskBuilder index(string $dataSource, \Level23\Druid\InputSources\InputSourceInterface $inputSource)
 * @method static \Level23\Druid\Tasks\IndexTaskBuilder reindex(string $dataSource)
 *
 * @package Level23\Druid\Facades
 * @codeCoverageIgnore
 */
class Druid extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'druid';
    }
}