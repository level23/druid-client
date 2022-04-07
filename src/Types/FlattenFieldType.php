<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use InvalidArgumentException;

/**
 * Class FlattenFieldType
 *
 * @package Level23\Druid\Types
 */
final class FlattenFieldType extends Enum
{
    /**
     * root, referring to a field at the root level of the record. Only really useful if useFieldDiscovery is false.
     */
    public const ROOT = 'root';
    /**
     * referring to a field using JsonPath notation. Supported by most data formats that offer nesting, including avro,
     * json, orc, and parquet.
     *
     * @see https://github.com/json-path/JsonPath
     */
    public const PATH = 'path';
    /**
     * referring to a field using jackson-jq notation. Only supported for the json format
     *
     * @see https://github.com/eiiches/jackson-jq
     */
    public const JQ = 'jq';

    /**
     * @param string $flattenFieldType
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function validate(string $flattenFieldType): string
    {
        $flattenFieldType = strtolower($flattenFieldType);
        if (!FlattenFieldType::isValidValue($flattenFieldType)) {
            throw new InvalidArgumentException(
                'The given flatten field type is invalid: ' . $flattenFieldType . '. ' .
                'Allowed are: ' . implode(',', FlattenFieldType::values())
            );
        }

        return $flattenFieldType;
    }
}