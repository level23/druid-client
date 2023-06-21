<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum ArithmeticFunction
 *
 * @package Level23\Druid\Types
 */
enum ArithmeticFunction: string
{
    case ADD      = '+';
    case DIVIDE   = '/';
    case MULTIPLY = '*';
    case SUBTRACT = '-';
    case QUOTIENT = 'quotient';
}