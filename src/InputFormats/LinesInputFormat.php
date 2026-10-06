<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

/**
 * Reads each line as UTF-8 text into a single column named "line". Requires Druid 35 or higher.
 *
 * @see https://druid.apache.org/docs/latest/ingestion/data-formats#lines
 */
class LinesInputFormat implements InputFormatInterface
{
    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return ['type' => 'lines'];
    }
}
