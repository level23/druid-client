<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class OrderByDirection
 *
 * @package Level23\Druid\Types
 */
final class OrderByDirection extends Enum
{
    public const ASC  = 'ascending';
    public const DESC = 'descending';

    /**
     * @param string $direction
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate(string $direction): string
    {
        $direction = strtolower($direction);
        if ($direction == 'asc') {
            $direction = OrderByDirection::ASC;
        } elseif ($direction == 'desc') {
            $direction = OrderByDirection::DESC;
        }

        if (!OrderByDirection::isValidValue($direction)) {
            throw new InvalidArgumentException(
                'Invalid order by direction given: ' . $direction .
                '. Valid options are: ' . implode(', ', OrderByDirection::values())
            );
        }

        return $direction;
    }
}