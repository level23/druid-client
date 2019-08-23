<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use MyCLabs\Enum\Enum;
use InvalidArgumentException;

/**
 * Class SortingOrder
 *
 * @method static self LEXICOGRAPHIC()
 * @method static self ALPHANUMERIC()
 * @method static self NUMERIC()
 * @method static self STRLEN()
 * @method static self VERSION()
 *
 * @package Level23\Druid\Types
 * @codeCoverageIgnore
 */
class SortingOrder extends Enum
{
    private const LEXICOGRAPHIC = 'lexicographic';
    private const ALPHANUMERIC  = 'alphanumeric';
    private const NUMERIC       = 'numeric';
    private const STRLEN        = 'strlen';
    private const VERSION       = 'version';

    /**
     * @param string|\Level23\Druid\Types\SortingOrder $ordering
     */
    public static function validate(&$ordering)
    {
        if (is_string($ordering) && !SortingOrder::isValid($ordering = strtolower($ordering))) {
            throw new InvalidArgumentException(
                'The given sorting order is invalid: ' . $ordering . '. ' .
                'Allowed are: ' . implode(',', SortingOrder::values())
            );
        }
    }
}