<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

class AvroOcfInputFormat implements InputFormatInterface
{
    protected ?FlattenSpec $flattenSpec;

    /**
     * @var array<string,mixed>|null
     */
    protected ?array $schema;

    protected ?bool $binaryAsString;

    protected ?bool $extractUnionsByType;

    /**
     * @param FlattenSpec|null         $flattenSpec          Specifies flattening configuration for nested Avro data.
     * @param array<string,mixed>|null $schema               Optional reader schema as an Avro JSON record. When omitted
     *                                                       the writer schema embedded in the OCF file is used.
     * @param bool|null                $binaryAsString       Treat binary Avro columns as UTF-8 strings. Default false.
     * @param bool|null                $extractUnionsByType  Extract Avro union fields as a structured object keyed by
     *                                                       the union member type instead of the raw value. Default
     *                                                       false.
     *
     * @see https://druid.apache.org/docs/latest/ingestion/data-formats#avro-ocf
     */
    public function __construct(
        ?FlattenSpec $flattenSpec = null,
        ?array $schema = null,
        ?bool $binaryAsString = null,
        ?bool $extractUnionsByType = null
    ) {
        $this->flattenSpec         = $flattenSpec;
        $this->schema              = $schema;
        $this->binaryAsString      = $binaryAsString;
        $this->extractUnionsByType = $extractUnionsByType;
    }

    /**
     * @return array<string,string|bool|array<mixed>>
     */
    public function toArray(): array
    {
        $result = ['type' => 'avro_ocf'];

        if ($this->flattenSpec !== null) {
            $result['flattenSpec'] = $this->flattenSpec->toArray();
        }

        if ($this->schema !== null) {
            $result['schema'] = $this->schema;
        }

        if ($this->binaryAsString !== null) {
            $result['binaryAsString'] = $this->binaryAsString;
        }

        if ($this->extractUnionsByType !== null) {
            $result['extractUnionsByType'] = $this->extractUnionsByType;
        }

        return $result;
    }
}
