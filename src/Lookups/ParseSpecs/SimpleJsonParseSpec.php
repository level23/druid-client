<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups\ParseSpecs;

/**
 * @internal
 */
class SimpleJsonParseSpec implements ParseSpecInterface
{
    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'format' => 'simpleJson',
        ];
    }
}