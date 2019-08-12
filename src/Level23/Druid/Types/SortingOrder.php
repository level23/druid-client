<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use MyCLabs\Enum\Enum;

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
 */
class SortingOrder extends Enum
{
    private const LEXICOGRAPHIC = 'lexicographic';
    private const ALPHANUMERIC = 'alphanumeric';
    private const NUMERIC      = 'numeric';
    private const STRLEN       = 'strlen';
    private const VERSION      = 'version';
}