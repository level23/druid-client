<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

use Exception;

class InlineInputSource implements InputSourceInterface
{
    protected string $data;

    /**
     * InlineInputSource constructor.
     *
     * @param string $data
     */
    public function __construct(string $data)
    {
        $this->data = $data;
    }

    /**
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'type' => 'inline',
            'data' => $this->data,
        ];
    }

    /**
     * Helper method to convert array to a json string.
     *
     * @param array $data
     *
     * @return string
     */
    public static function dataToJson(array $data): string
    {
        return implode("\n", array_map(fn($elem) => json_encode($elem), $data));
    }

    /**
     * Helper method to convert an array to a csv string.
     *
     * @param array  $data
     * @param string $separator
     * @param string $enclosure
     * @param string $escape
     *
     * @return string
     * @throws \Exception
     */
    public static function dataToCsv(
        array $data,
        string $separator = ",",
        string $enclosure = '"',
        string $escape = "\\"
    ): string {
        $f = fopen('php://memory', 'r+');
        if (!$f) {
            throw new Exception('Failed to convert data to csv'); // @codeCoverageIgnore
        }
        foreach ($data as $row) {
            fputcsv($f, $row, $separator, $enclosure, $escape);
        }
        rewind($f);

        $content = stream_get_contents($f);
        if ($content === false) {
            throw new Exception('Failed to convert data to csv'); // @codeCoverageIgnore
        }
        fclose($f);

        return $content;
    }
}