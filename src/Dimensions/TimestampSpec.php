<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

class TimestampSpec
{
    protected string $column;

    protected string $format;

    /**
     * @var string|null
     */
    protected ?string $missingValue = null;

    public function __construct(string $column, string $format, ?string $missingValue = null)
    {
        $this->column       = $column;
        $this->format       = $format;
        $this->missingValue = $missingValue;
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        $response = [
            'column' => $this->column,
            'format' => $this->format,
        ];

        if (!empty($this->missingValue)) {
            $response['missingValue'] = $this->missingValue;
        }

        return $response;
    }
}