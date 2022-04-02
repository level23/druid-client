<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class InputFormat
 *
 * @package Level23\Druid\Types
 */
final class InputFormat extends Enum
{
    public const CSV            = 'csv';
    public const JSON           = 'json';

    /**
     * @param string $inputFormat
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate(string $inputFormat): string
    {
        $inputFormat = strtolower($inputFormat);
        if (!InputFormat::isValidValue($inputFormat)) {
            throw new InvalidArgumentException(
                'The given input format is invalid: ' . $inputFormat . '. ' .
                'Allowed are: ' . implode(',', InputFormat::values())
            );
        }

        return $inputFormat;
    }
}