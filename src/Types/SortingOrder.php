<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class SortingOrder
 *
 * @package Level23\Druid\Types
 */
final class SortingOrder extends Enum
{
    public const LEXICOGRAPHIC = 'lexicographic';
    public const ALPHANUMERIC  = 'alphanumeric';
    public const NUMERIC       = 'numeric';
    public const STRLEN        = 'strlen';
    public const VERSION       = 'version';

    /**
     * @param string $ordering
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate($ordering)
    {
        $ordering = strtolower($ordering);

        if (!SortingOrder::isValidValue($ordering)) {
            throw new InvalidArgumentException(
                'The given sorting order is invalid: ' . $ordering . '. ' .
                'Allowed are: ' . implode(',', SortingOrder::values())
            );
        }

        return $ordering;
    }
}