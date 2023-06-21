<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum OrderByDirection
 *
 * @package Level23\Druid\Types
 */
enum OrderByDirection: string
{
    case ASC = 'ascending';
    case DESC = 'descending';

    public static function make(string $value): OrderByDirection
    {
        $value = strtolower($value);
        if ($value == 'asc') {
            return self::ASC;
        } elseif ($value == 'desc') {
            return self::DESC;
        }

        return self::from($value);
    }
}