<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum TimeBound
 *
 * @package Level23\Druid\Types
 */
enum TimeBound: string
{
    case MAX_TIME = 'maxTime';
    case MIN_TIME = 'minTime';
    case BOTH = 'both';
}