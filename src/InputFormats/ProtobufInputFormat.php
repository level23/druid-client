<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

class ProtobufInputFormat implements InputFormatInterface
{
    protected array $protoBytesDecoder;

    /**
     * @var \Level23\Druid\InputFormats\FlattenSpec|null
     */
    protected ?FlattenSpec $flattenSpec;

    /**
     *
     * @param array            $protoBytesDecoder Specifies how to decode bytes to Protobuf record. See below for an
     *                                            example.
     * @param FlattenSpec|null $flattenSpec       Define a flattenSpec to extract nested values from a Parquet file.
     *                                            Note that only 'path' expression are supported ('jq' is unavailable).
     *
     * Example $protoBytesDecoder value:
     * ```
     * [
     *     "type" => "file",
     *     "descriptor" => "file:///tmp/metrics.desc",
     *     "protoMessageType" => "Metrics"
     * ]
     * ```
     *
     * @see https://druid.apache.org/docs/latest/ingestion/data-formats.html#protobuf
     */
    public function __construct(array $protoBytesDecoder, FlattenSpec $flattenSpec = null)
    {
        $this->protoBytesDecoder = $protoBytesDecoder;
        $this->flattenSpec       = $flattenSpec;
    }

    /**
     * Return the ProtobufInputFormat so that it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type'              => 'protobuf',
            'protoBytesDecoder' => $this->protoBytesDecoder,
        ];

        if ($this->flattenSpec) {
            $result['flattenSpec'] = $this->flattenSpec->toArray();
        }

        return $result;
    }
}