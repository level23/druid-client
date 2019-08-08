<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use MyCLabs\Enum\Enum;

/**
 * Class BoundOperator
 *
 * @method static self GT()
 * @method static self GE()
 * @method static self LT()
 * @method static self LE()
 *
 * @package Level23\Druid\Types
 */
class BoundOperator extends Enum
{
    private const GT = '>';
    private const GE = '>=';
    private const LT = '<';
    private const LE = '<=';
}