<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum Granularity
 *
 * @package Level23\Druid\Types
 */
enum Granularity: string
{
    case ALL            = 'all';
    case NONE           = 'none';
    case SECOND         = 'second';
    case MINUTE         = 'minute';
    case FIFTEEN_MINUTE = 'fifteen_minute';
    case THIRTY_MINUTE  = 'thirty_minute';
    case HOUR           = 'hour';
    case DAY            = 'day';
    case WEEK           = 'week';
    case MONTH          = 'month';
    case QUARTER        = 'quarter';
    case YEAR           = 'year';
}