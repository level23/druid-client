<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class ArithmeticFunction
 *
 * @package Level23\Druid\Types
 * @codeCoverageIgnore
 */
final class ArithmeticFunction extends Enum
{
    public const ADD      = '+';
    public const DIVIDE   = '/';
    public const MULTIPLY = '*';
    public const SUBTRACT = '-';
    public const QUOTIENT = 'quotient';

    /**
     * @param string $operator
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate($operator)
    {
        if (is_string($operator) && !ArithmeticFunction::isValidValue($operator = strtolower($operator))) {
            throw new InvalidArgumentException(
                'Invalid operator given: ' . $operator .
                '. Valid options are: ' . implode(',', ArithmeticFunction::values())
            );
        }

        return $operator;
    }
}