<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Extractions\ExtractionInterface;

class InFilter implements FilterInterface
{
    protected string $dimension;

    /**
     * @var string[]|int[]
     */
    protected array $values;

    protected ?ExtractionInterface $extraction;

    /**
     * InFilter constructor.
     *
     * @param string                   $dimension
     * @param string[]|int[]           $values
     * @param ExtractionInterface|null $extraction
     */
    public function __construct(string $dimension, array $values, ?ExtractionInterface $extraction = null)
    {
        $this->values     = $values;
        $this->dimension  = $dimension;
        $this->extraction = $extraction;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<int|string>|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        $result = [
            'type'      => 'in',
            'dimension' => $this->dimension,
            'values'    => array_values($this->values),
        ];

        if ($this->extraction) {
            $result['extractionFn'] = $this->extraction->toArray();
        }

        return $result;
    }
}