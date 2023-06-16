<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum MultiValueHandling
 *
 * @package Level23\Druid\Types
 */
enum MultiValueHandling: string
{
    case SORTED_ARRAY = 'SORTED_ARRAY';
    case SORTED_SET   = 'SORTED_SET';
    case ARRAY        = 'ARRAY';
}