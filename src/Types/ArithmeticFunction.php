<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use MyCLabs\Enum\Enum;
use InvalidArgumentException;

/**
 * Class ArithmeticFunction
 *
 * @method static self ADD()
 * @method static self DIVIDE()
 * @method static self MULTIPLY()
 * @method static self SUBTRACT()
 * @method static self QUOTIENT()
 *
 * @package Level23\Druid\Types
 * @codeCoverageIgnore
 */
class ArithmeticFunction extends Enum
{
    private const ADD      = '+';
    private const DIVIDE   = '/';
    private const MULTIPLY = '*';
    private const SUBTRACT = '-';
    private const QUOTIENT = 'quotient';

    /**
     * @param string|\Level23\Druid\Types\ArithmeticFunction $operator
     *
     * @return string|\Level23\Druid\Types\ArithmeticFunction
     * @throws InvalidArgumentException
     */
    public static function validate($operator)
    {
        if (is_string($operator) && !ArithmeticFunction::isValid($operator = strtolower($operator))) {
            throw new InvalidArgumentException(
                'Invalid operator given: ' . $operator .
                '. Valid options are: ' . implode(',', ArithmeticFunction::values())
            );
        }

        return $operator;
    }
}