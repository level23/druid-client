<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class Granularity
 *
 * @package Level23\Druid\Types
 */
final class Granularity extends Enum
{
    public const ALL            = 'all';
    public const NONE           = 'none';
    public const SECOND         = 'second';
    public const MINUTE         = 'minute';
    public const FIFTEEN_MINUTE = 'fifteen_minute';
    public const THIRTY_MINUTE  = 'thirty_minute';
    public const HOUR           = 'hour';
    public const DAY            = 'day';
    public const WEEK           = 'week';
    public const MONTH          = 'month';
    public const QUARTER        = 'quarter';
    public const YEAR           = 'year';

    /**
     * @param string $granularity
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate(string $granularity): string
    {
        $granularity = strtolower($granularity);
        if (!Granularity::isValidValue($granularity)) {
            throw new InvalidArgumentException(
                'The given granularity is invalid: ' . $granularity . '. ' .
                'Allowed are: ' . implode(',', Granularity::values())
            );
        }

        return $granularity;
    }
}