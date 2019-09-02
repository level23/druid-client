<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use MyCLabs\Enum\Enum;
use InvalidArgumentException;

/**
 * Class OrderByDirection
 *
 * @method static self ASC()
 * @method static self DESC()
 *
 * @package Level23\Druid\Types
 * @codeCoverageIgnore
 */
class OrderByDirection extends Enum
{
    private const ASC  = 'ascending';
    private const DESC = 'descending';

    /**
     * @param string|\Level23\Druid\Types\OrderByDirection $direction
     *
     * @return string|\Level23\Druid\Types\OrderByDirection
     * @throws InvalidArgumentException
     */
    public static function validate($direction)
    {
        if (is_string($direction) && !OrderByDirection::isValid($direction = strtolower($direction))) {
            throw new InvalidArgumentException(
                'Invalid order by direction given: ' . $direction .
                '. Valid options are: ' . implode(', ', OrderByDirection::values())
            );
        }

        return $direction;
    }
}