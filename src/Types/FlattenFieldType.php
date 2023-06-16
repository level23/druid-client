<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

/**
 * Enum FlattenFieldType
 *
 * @package Level23\Druid\Types
 */
enum FlattenFieldType: string
{
    /**
     * root, referring to a field at the root level of the record. Only really useful if useFieldDiscovery is false.
     */
    case ROOT = 'root';
    /**
     * referring to a field using JsonPath notation. Supported by most data formats that offer nesting, including avro,
     * json, orc, and parquet.
     *
     * @see https://github.com/json-path/JsonPath
     */
    case PATH = 'path';
    /**
     * referring to a field using jackson-jq notation. Only supported for the json format
     *
     * @see https://github.com/eiiches/jackson-jq
     */
    case JQ = 'jq';
}