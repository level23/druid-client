<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class MultiValueHandling
 *
 * @package Level23\Druid\Types
 */
final class MultiValueHandling extends Enum
{
    public const SORTED_ARRAY = 'SORTED_ARRAY';
    public const SORTED_SET   = 'SORTED_SET';
    public const ARRAY        = 'ARRAY';

    /**
     * @param string $multiValueHandling
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate(string $multiValueHandling): string
    {
        $multiValueHandling = strtoupper($multiValueHandling);
        if (!MultiValueHandling::isValidValue($multiValueHandling)) {
            throw new InvalidArgumentException(
                'The given MultiValueHandling type is invalid: ' . $multiValueHandling . '. ' .
                'Allowed are: ' . implode(', ', MultiValueHandling::values())
            );
        }

        return $multiValueHandling;
    }
}