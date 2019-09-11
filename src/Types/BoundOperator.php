<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class BoundOperator
 *
 * @package Level23\Druid\Types
 */
final class BoundOperator extends Enum
{
    public const GT = '>';
    public const GE = '>=';
    public const LT = '<';
    public const LE = '<=';

    /**
     * @param string $operator
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate($operator)
    {
        if (!BoundOperator::isValidValue($operator)) {
            throw new InvalidArgumentException(
                'Invalid operator given: ' . $operator .
                '. Valid options are: ' . implode(',', BoundOperator::values())
            );
        }

        return $operator;
    }
}