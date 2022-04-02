<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class NullHandling
 *
 * @package Level23\Druid\Types
 */
class NullHandling extends Enum
{
    public const NULL_STRING  = 'nullString';
    public const EMPTY_STRING = 'emptyString';
    public const RETURN_NULL  = 'returnNull';

    /**
     * @param string $nullHandling
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate(string $nullHandling): string
    {
        if (!NullHandling::isValidValue($nullHandling)) {
            throw new InvalidArgumentException(
                'The given NullHandling value is invalid: ' . $nullHandling . '. ' .
                'Allowed are: ' . implode(',', NullHandling::values())
            );
        }

        return $nullHandling;
    }
}