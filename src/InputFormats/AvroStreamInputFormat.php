<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

class AvroStreamInputFormat implements InputFormatInterface
{
    /**
     * @var array<string,mixed>
     */
    protected array $avroBytesDecoder;

    protected ?FlattenSpec $flattenSpec;

    protected ?bool $binaryAsString;

    protected ?bool $extractUnionsByType;

    /**
     * @param array<string,mixed> $avroBytesDecoder     Specifies how to decode bytes into an Avro record. The shape
     *                                                  depends on the decoder type (e.g. schema_registry,
     *                                                  schema_inline, schema_repo).
     * @param FlattenSpec|null    $flattenSpec          Specifies flattening configuration for nested Avro data.
     * @param bool|null           $binaryAsString       Treat binary Avro columns as UTF-8 strings. Default false.
     * @param bool|null           $extractUnionsByType  Extract Avro union fields as a structured object keyed by the
     *                                                  union member type instead of the raw value. Default false.
     *
     * @see https://druid.apache.org/docs/latest/ingestion/data-formats#avro-stream
     */
    public function __construct(
        array $avroBytesDecoder,
        ?FlattenSpec $flattenSpec = null,
        ?bool $binaryAsString = null,
        ?bool $extractUnionsByType = null
    ) {
        $this->avroBytesDecoder    = $avroBytesDecoder;
        $this->flattenSpec         = $flattenSpec;
        $this->binaryAsString      = $binaryAsString;
        $this->extractUnionsByType = $extractUnionsByType;
    }

    /**
     * @return array<string,string|bool|array<mixed>>
     */
    public function toArray(): array
    {
        $result = [
            'type'             => 'avro_stream',
            'avroBytesDecoder' => $this->avroBytesDecoder,
        ];

        if ($this->flattenSpec !== null) {
            $result['flattenSpec'] = $this->flattenSpec->toArray();
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
