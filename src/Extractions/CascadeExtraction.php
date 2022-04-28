<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class CascadeExtraction implements ExtractionInterface
{
    /**
     * @var \Level23\Druid\Extractions\ExtractionInterface[]
     */
    protected array $extractions = [];

    /**
     * CascadeExtraction constructor.
     *
     * @param \Level23\Druid\Extractions\ExtractionInterface ...$extractions
     */
    public function __construct(...$extractions)
    {
        $this->extractions = $extractions;
    }

    /**
     * Add an extraction function to our list of functions to apply.
     *
     * @param \Level23\Druid\Extractions\ExtractionInterface $extraction
     */
    public function addExtraction(ExtractionInterface $extraction): void
    {
        $this->extractions[] = $extraction;
    }

    /**
     * Return the Extraction Function, so it can be used in a druid query.
     *
     * @return array<string,string|array<array<string,string|int|bool|array<mixed>>>>
     */
    public function toArray(): array
    {
        return [
            'type'          => 'cascade',
            'extractionFns' => array_map(fn(ExtractionInterface $extraction) => $extraction->toArray(), $this->extractions),
        ];
    }
}