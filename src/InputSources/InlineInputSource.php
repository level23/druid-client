<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

use Exception;
use Level23\Druid\Types\InputFormat;

class InlineInputSource implements InputSourceInterface
{
    protected array $data;

    protected string $inputFormat;

    /**
     * @return string
     */
    public function getInputFormat(): string
    {
        return $this->inputFormat;
    }

    /**
     * InlineInputSource constructor.
     *
     * @param array  $data
     * @param string $inputFormat
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data, string $inputFormat = InputFormat::JSON)
    {
        InputFormat::validate($inputFormat);

        $this->data        = $data;
        $this->inputFormat = $inputFormat;
    }

    /**
     * @throws \Exception
     */
    public function toArray(): array
    {
        if ($this->inputFormat == InputFormat::JSON) {
            $encodedData = $this->dataToJson($this->data);
        } else {
            $encodedData = $this->dataToCsv($this->data);
        }

        return [
            'type' => 'inline',
            'data' => $encodedData,
        ];
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function dataToJson(array $data): string
    {
        return implode("\n", array_map(fn($elem) => json_encode($elem), $data));
    }

    /**
     * @param array $data
     *
     * @return string
     * @throws \Exception
     */
    protected function dataToCsv(array $data): string
    {
        $f = fopen('php://memory', 'r+');
        if (!$f) {
            throw new Exception('Failed to convert data to csv'); // @codeCoverageIgnore
        }
        foreach ($data as $row) {
            fputcsv($f, $row);
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