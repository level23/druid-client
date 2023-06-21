<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum BoundOperator
 *
 * @package Level23\Druid\Types
 */
enum BoundOperator: string
{
    case GT = '>';
    case GE = '>=';
    case LT = '<';
    case LE = '<=';
}