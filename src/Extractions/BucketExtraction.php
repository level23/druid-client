<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class BucketExtraction implements ExtractionInterface
{
    protected int $size;

    protected int $offset;

    public function __construct(int $size = 1, int $offset = 0)
    {
        $this->size   = $size;
        $this->offset = $offset;
    }

    /**
     * Return the Extraction Function, so it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'   => 'bucket',
            'size'   => $this->size,
            'offset' => $this->offset,
        ];
    }
}
