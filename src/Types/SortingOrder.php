<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum SortingOrder
 *
 * @package Level23\Druid\Types
 */
enum SortingOrder: string
{
    case LEXICOGRAPHIC = 'lexicographic';
    case ALPHANUMERIC  = 'alphanumeric';
    case NUMERIC       = 'numeric';
    case STRLEN        = 'strlen';
    case VERSION       = 'version';
}