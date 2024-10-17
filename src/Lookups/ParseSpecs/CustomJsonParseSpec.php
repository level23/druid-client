<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups\ParseSpecs;

/**
 * @internal
 */
class CustomJsonParseSpec implements ParseSpecInterface
{
    /**
     * Specify the parse specification to be a json file.
     *
     * @param string $keyFieldName   The field name of the key
     * @param string $valueFieldName The field name of the value
     */
    public function __construct(
        protected string $keyFieldName,
        protected string $valueFieldName,
    ) {

    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'format'         => 'customJson',
            'keyFieldName'   => $this->keyFieldName,
            'valueFieldName' => $this->valueFieldName,
        ];
    }
}