<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\InputFormats\FlattenSpec;
use Level23\Druid\InputFormats\CsvInputFormat;
use Level23\Druid\InputFormats\TsvInputFormat;
use Level23\Druid\InputFormats\OrcInputFormat;
use Level23\Druid\InputFormats\JsonInputFormat;
use Level23\Druid\InputFormats\ParquetInputFormat;
use Level23\Druid\InputFormats\ProtobufInputFormat;
use Level23\Druid\InputFormats\InputFormatInterface;

trait HasInputFormat
{
    /**
     * @var \Level23\Druid\InputFormats\InputFormatInterface|null
     */
    protected ?InputFormatInterface $inputFormat = null;

    /**
     * Specify that we use JSON as input format.
     *
     * @param FlattenSpec|null        $flattenSpec Specifies flattening configuration for nested JSON data. See
     *                                             flattenSpec for more info.
     * @param array<string,bool>|null $features    JSON parser features supported by Jackson library. Those features
     *                                             will be applied when parsing the input JSON data.
     *
     * @see https://github.com/FasterXML/jackson-core/wiki/JsonParser-Features
     */
    public function jsonFormat(?FlattenSpec $flattenSpec = null, ?array $features = null): self
    {
        $this->inputFormat = new JsonInputFormat($flattenSpec, $features);

        return $this;
    }

    /**
     * Specify that we use CSV as input format.
     *
     * @param string[]|null $columns               Specifies the columns of the data. The columns should be in the same
     *                                             order with the columns of your data.
     * @param string|null   $listDelimiter         A custom delimiter for multi-value dimensions.
     * @param bool|null     $findColumnsFromHeader If this is set, the task will find the column names from the header
     *                                             row. Note that skipHeaderRows will be applied before finding column
     *                                             names from the header. For example, if you set skipHeaderRows to 2
     *                                             and findColumnsFromHeader to true, the task will skip the first two
     *                                             lines and then extract column information from the third line.
     *                                             columns will be ignored if this is set to true.
     * @param int           $skipHeaderRows        If this is set, the task will skip the first skipHeaderRows rows.
     */
    public function csvFormat(
        ?array $columns = null,
        ?string $listDelimiter = null,
        ?bool $findColumnsFromHeader = null,
        int $skipHeaderRows = 0
    ): self {
        $this->inputFormat = new CsvInputFormat($columns, $listDelimiter, $findColumnsFromHeader, $skipHeaderRows);

        return $this;
    }

    /**
     * Specify that we use TSV as input format.
     *
     * @param array<string>|null $columns               Specifies the columns of the data. The columns should be in the
     *                                                  same order with the columns of your data.
     * @param string|null        $delimiter             A custom delimiter for data values.
     * @param string|null        $listDelimiter         A custom delimiter for multi-value dimensions.
     * @param bool|null          $findColumnsFromHeader If this is set, the task will find the column names from the
     *                                                  header row. Note that skipHeaderRows will be applied before
     *                                                  finding column names from the header. For example, if you set
     *                                                  skipHeaderRows to 2 and findColumnsFromHeader to true, the task
     *                                                  will skip the first two lines and then extract column
     *                                                  information from the third line. columns will be ignored if
     *                                                  this is set to true.
     * @param int                $skipHeaderRows        If this is set, the task will skip the first skipHeaderRows
     *                                                  rows.
     */
    public function tsvFormat(
        ?array $columns = null,
        ?string $delimiter = null,
        ?string $listDelimiter = null,
        ?bool $findColumnsFromHeader = null,
        int $skipHeaderRows = 0
    ): self {
        $this->inputFormat = new TsvInputFormat(
            $columns,
            $delimiter,
            $listDelimiter,
            $findColumnsFromHeader,
            $skipHeaderRows
        );

        return $this;
    }

    /**
     * Specify that we use ORC as input format.
     *
     * To use the ORC input format, load the Druid Orc extension ( druid-orc-extensions).
     *
     * @param FlattenSpec|null $flattenSpec    Specifies flattening configuration for nested ORC data. See flattenSpec
     *                                         for more info.
     * @param bool|null        $binaryAsString Specifies if the binary orc column which is not logically marked as a
     *                                         string should be treated as a UTF-8 encoded string. Default is false.
     */
    public function orcFormat(?FlattenSpec $flattenSpec = null, ?bool $binaryAsString = null): self
    {
        $this->inputFormat = new OrcInputFormat($flattenSpec, $binaryAsString);

        return $this;
    }

    /**
     * Specify that we use Parquet as input format.
     *
     * To use the Parquet input format load the Druid Parquet extension (druid-parquet-extensions).
     *
     * @param FlattenSpec|null $flattenSpec    Define a flattenSpec to extract nested values from a Parquet file. Note
     *                                         that only 'path' expression are supported ('jq' is unavailable).
     * @param bool|null        $binaryAsString Specifies if the bytes parquet column which is not logically marked as a
     *                                         string or enum type should be treated as a UTF-8 encoded string.
     */
    public function parquetFormat(?FlattenSpec $flattenSpec = null, ?bool $binaryAsString = null): self
    {
        $this->inputFormat = new ParquetInputFormat($flattenSpec, $binaryAsString);

        return $this;
    }

    /**
     * Specify that we use Protobuf as input format.
     *
     * You need to include the druid-protobuf-extensions as an extension to use the Protobuf input format.
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
    public function protobufFormat(array $protoBytesDecoder, ?FlattenSpec $flattenSpec = null): self
    {
        $this->inputFormat = new ProtobufInputFormat($protoBytesDecoder, $flattenSpec);

        return $this;
    }
}