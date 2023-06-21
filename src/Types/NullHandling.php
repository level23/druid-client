<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum NullHandling
 *
 * @package Level23\Druid\Types
 */
enum NullHandling: string
{
    case NULL_STRING  = 'nullString';
    case EMPTY_STRING = 'emptyString';
    case RETURN_NULL  = 'returnNull';
}