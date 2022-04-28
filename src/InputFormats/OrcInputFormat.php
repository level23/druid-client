<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

class OrcInputFormat implements InputFormatInterface
{
    protected ?FlattenSpec $flattenSpec;

    protected ?bool $binaryAsString;

    /**
     * @param FlattenSpec|null $flattenSpec    Specifies flattening configuration for nested ORC data. See flattenSpec
     *                                         for more info.
     * @param bool|null        $binaryAsString Specifies if the binary orc column which is not logically marked as a
     *                                         string should be treated as a UTF-8 encoded string. Default is false.
     */
    public function __construct(FlattenSpec $flattenSpec = null, bool $binaryAsString = null)
    {
        $this->flattenSpec    = $flattenSpec;
        $this->binaryAsString = $binaryAsString;
    }

    /**
     * Return the OrcInputFormat so that it can be used in a druid query.
     *
     * @return array<string,string|array<string,bool|array<array<string,string>>>|bool>
     */
    public function toArray(): array
    {
        $result = ['type' => 'orc'];

        if (!empty($this->flattenSpec)) {
            $result['flattenSpec'] = $this->flattenSpec->toArray();
        }

        if ($this->binaryAsString !== null) {
            $result['binaryAsString'] = $this->binaryAsString;
        }

        return $result;
    }
}