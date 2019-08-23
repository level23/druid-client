<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use MyCLabs\Enum\Enum;
use InvalidArgumentException;

/**
 * Class DataType
 * @method static self STRING()
 * @method static self FLOAT()
 * @method static self LONG()
 * @method static self DOUBLE()
 *
 * @package Level23\Druid\Types
 * @codeCoverageIgnore
 */
class DataType extends Enum
{
    private const STRING = 'string';
    private const FLOAT  = 'float';
    private const LONG   = 'long';
    private const DOUBLE = 'double';

    /**
     * Validate the DataType.
     *
     * @param string|\Level23\Druid\Types\DataType $outputType
     */
    public static function validate(&$outputType)
    {
        if (is_string($outputType) && !self::isValid($outputType = strtolower($outputType))) {
            throw new InvalidArgumentException(
                'The given output type is invalid: ' . $outputType . '. ' .
                'Allowed are: ' . implode(',', DataType::values())
            );
        }
    }
}