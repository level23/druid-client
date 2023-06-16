<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum DataType
 *
 * @package Level23\Druid\Types
 */
enum DataType: string
{
    case STRING = 'string';
    case FLOAT  = 'float';
    case LONG   = 'long';
    case DOUBLE = 'double';
}