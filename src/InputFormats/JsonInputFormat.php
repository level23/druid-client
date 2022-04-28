<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

class JsonInputFormat implements InputFormatInterface
{
    /**
     * @var \Level23\Druid\InputFormats\FlattenSpec|null
     */
    protected ?FlattenSpec $flattenSpec;

    /**
     * @var array<string,bool>
     */
    protected ?array $features;

    /**
     * @param FlattenSpec|null        $flattenSpec  Specifies flattening configuration for nested JSON data. See
     *                                              flattenSpec for more info.
     * @param array<string,bool>|null $features     JSON parser features supported by Jackson library. Those features
     *                                              will be applied when parsing the input JSON data.
     *
     * @see https://github.com/FasterXML/jackson-core/wiki/JsonParser-Features
     */
    public function __construct(FlattenSpec $flattenSpec = null, array $features = null)
    {
        $this->flattenSpec = $flattenSpec;
        $this->features    = $features;
    }

    /**
     * Return the JsonInputFormat so that it can be used in a druid query.
     *
     * @return array<string,string|array<string,bool>|array<string,bool|array<array<string,string>>>>
     */
    public function toArray(): array
    {
        $result = ['type' => 'json'];

        if (!empty($this->flattenSpec)) {
            $result['flattenSpec'] = $this->flattenSpec->toArray();
        }

        if (!empty($this->features)) {
            $result['featureSpec'] = $this->features;
        }

        return $result;
    }
}