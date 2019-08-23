<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use MyCLabs\Enum\Enum;
use InvalidArgumentException;

/**
 * Class BoundOperator
 *
 * @method static self GT()
 * @method static self GE()
 * @method static self LT()
 * @method static self LE()
 *
 * @package Level23\Druid\Types
 * @codeCoverageIgnore
 */
class BoundOperator extends Enum
{
    private const GT = '>';
    private const GE = '>=';
    private const LT = '<';
    private const LE = '<=';

    /**
     * @param string|\Level23\Druid\Types\BoundOperator $operator
     */
    public static function validate($operator)
    {
        if (is_string($operator) && !BoundOperator::isValid($operator)) {
            throw new InvalidArgumentException(
                'Invalid operator given: ' . $operator .
                '. Valid options are: ' . implode(',', BoundOperator::values())
            );
        }
    }
}