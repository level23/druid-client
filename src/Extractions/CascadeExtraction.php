<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class CascadeExtraction implements ExtractionInterface
{
    /**
     * @var array|\Level23\Druid\Extractions\ExtractionInterface[]
     */
    protected $extractions = [];

    public function __construct(...$extractions)
    {
        $this->extractions = $extractions;
    }

    /**
     * Add an extraction function to our list of functions to apply.
     *
     * @param \Level23\Druid\Extractions\ExtractionInterface $extraction
     */
    public function addExtraction(ExtractionInterface $extraction)
    {
        $this->extractions[] = $extraction;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'          => 'cascade',
            'extractionFns' => array_map(function (ExtractionInterface $extraction) {
                return $extraction->toArray();
            }, $this->extractions),
        ];
    }
}