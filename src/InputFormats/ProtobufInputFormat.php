<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

class ProtobufInputFormat implements InputFormatInterface
{
    /**
     * @var array<string,string>
     */
    protected array $protoBytesDecoder;

    protected ?FlattenSpec $flattenSpec;

    /**
     *
     * @param array<string,string> $protoBytesDecoder Specifies how to decode bytes to Protobuf record. See below for
     *                                                an example.
     * @param FlattenSpec|null     $flattenSpec       Define a flattenSpec to extract nested values from a Parquet
     *                                                file. Note that only 'path' expression are supported ('jq' is
     *                                                unavailable).
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
    public function __construct(array $protoBytesDecoder, ?FlattenSpec $flattenSpec = null)
    {
        $this->protoBytesDecoder = $protoBytesDecoder;
        $this->flattenSpec       = $flattenSpec;
    }

    /**
     * Return the ProtobufInputFormat so that it can be used in a druid query.
     *
     * @return array<string,string|array<string,bool|array<array<string,string>>>|string[]>
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