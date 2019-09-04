<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class DataType
 *
 * @package Level23\Druid\Types
 * @codeCoverageIgnore
 */
final class DataType extends Enum
{
    public const STRING = 'string';
    public const FLOAT  = 'float';
    public const LONG   = 'long';
    public const DOUBLE = 'double';

    /**
     * Validate the DataType.
     *
     * @param string $outputType
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate($outputType)
    {
        if (is_string($outputType) && !self::isValidValue($outputType = strtolower($outputType))) {
            throw new InvalidArgumentException(
                'The given output type is invalid: ' . $outputType . '. ' .
                'Allowed are: ' . implode(',', DataType::values())
            );
        }

        return $outputType;
    }
}