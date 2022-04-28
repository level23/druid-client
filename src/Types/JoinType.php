<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class FlattenFieldType
 *
 * @package Level23\Druid\Types
 */
final class JoinType extends Enum
{
    public const INNER = 'INNER';
    public const LEFT  = 'LEFT';

    /**
     * @param string $joinType
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate(string $joinType): string
    {
        $joinType = strtolower($joinType);
        if (!JoinType::isValidValue($joinType)) {
            throw new InvalidArgumentException(
                'The given join type is invalid: ' . $joinType . '. ' .
                'Allowed are: ' . implode(', ', JoinType::values())
            );
        }

        return $joinType;
    }
}