<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum JoinType
 *
 * @package Level23\Druid\Types
 */
enum JoinType: string
{
    case INNER = 'INNER';
    case LEFT  = 'LEFT';
}