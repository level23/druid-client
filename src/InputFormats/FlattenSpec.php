<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

use Level23\Druid\Types\FlattenFieldType;

class FlattenSpec
{
    protected bool $useFieldDiscovery = true;

    /**
     * @var array<array<string,string>>
     */
    protected array $fields = [];

    public function __construct(bool $useFieldDiscovery = true)
    {
        $this->useFieldDiscovery = $useFieldDiscovery;
    }

    /**
     * @param string      $flattenFieldType One of the FlattenFieldType::* constant.s
     *                                      Valid options are:
     *                                      root, referring to a field at the root level of the record. Only really
     *                                      useful if useFieldDiscovery is false. path, referring to a field using
     *                                      JsonPath notation. Supported by most data formats that offer nesting,
     *                                      including avro, json, orc, and parquet. jq, referring to a field using
     *                                      jackson-jq notation. Only supported for the json format.
     * @param string      $name             Name of the field after flattening. This name can be referred to by the
     *                                      timestampSpec, transformSpec, dimensionsSpec, and metricsSpec.
     * @param string|null $expr             Expression for accessing the field while flattening. For type path, this
     *                                      should be JsonPath. For type jq, this should be jackson-jq notation. For
     *                                      other types, this parameter is ignored.
     *
     * @return $this
     */
    public function field(string $flattenFieldType, string $name, string $expr = null): self
    {
        $type = FlattenFieldType::validate($flattenFieldType);

        if (($type == FlattenFieldType::JQ || $type == FlattenFieldType::PATH) && empty($expr)) {
            throw new \InvalidArgumentException('For type JQ or PATH, you need to specify the expression!');
        }
        $field = [
            'type' => $type,
            'name' => $name,
        ];

        if (!empty($expr)) {
            $field['expr'] = $expr;
        }

        $this->fields[] = $field;

        return $this;
    }

    /**
     * Return the FlattenSpec so that it can be used in a druid query.
     *
     * @return array<string,bool|array<array<string,string>>>
     */
    public function toArray(): array
    {
        return [
            'useFieldDiscovery' => $this->useFieldDiscovery,
            'fields'            => $this->fields,
        ];
    }
}