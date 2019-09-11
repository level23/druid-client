<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

class ScanQueryResultFormat extends Enum
{
    public const NORMAL_LIST    = 'list';
    public const COMPACTED_LIST = 'compactedList';

    /**
     * @param string $resultFormat
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate($resultFormat)
    {
        $resultFormat = strtolower($resultFormat);
        if (!OrderByDirection::isValidValue($resultFormat)) {
            throw new InvalidArgumentException(
                'Invalid scanQuery resultFormat given: ' . $resultFormat .
                '. Valid options are: ' . implode(', ', ScanQueryResultFormat::values())
            );
        }

        return $resultFormat;
    }
}
