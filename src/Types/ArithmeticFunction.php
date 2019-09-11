<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class ArithmeticFunction
 *
 * @package Level23\Druid\Types
 */
final class ArithmeticFunction extends Enum
{
    public const ADD      = '+';
    public const DIVIDE   = '/';
    public const MULTIPLY = '*';
    public const SUBTRACT = '-';
    public const QUOTIENT = 'quotient';

    /**
     * @param string $function
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate($function)
    {
        $function = strtolower($function);
        if (!ArithmeticFunction::isValidValue($function)) {
            throw new InvalidArgumentException(
                'Invalid arithmetic function given: ' . $function .
                '. Valid options are: ' . implode(',', ArithmeticFunction::values())
            );
        }

        return $function;
    }
}