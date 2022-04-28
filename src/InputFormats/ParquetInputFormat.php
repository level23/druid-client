<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

class ParquetInputFormat extends OrcInputFormat
{
    /**
     * @param FlattenSpec|null $flattenSpec    Define a flattenSpec to extract nested values from a Parquet file. Note
     *                                         that only 'path' expression are supported ('jq' is unavailable).
     * @param bool|null        $binaryAsString Specifies if the bytes parquet column which is not logically marked as a
     *                                         string or enum type should be treated as a UTF-8 encoded string.
     */
    public function __construct(FlattenSpec $flattenSpec = null, bool $binaryAsString = null)
    {
        parent::__construct($flattenSpec, $binaryAsString);
    }

    /**
     * Return the ParquetInputFormat so that it can be used in a druid query.
     *
     * @return array<string,string|array<string,bool|array<array<string,string>>>|bool>
     */
    public function toArray(): array
    {
        $result         = parent::toArray();
        $result['type'] = 'parquet';

        return $result;
    }
}