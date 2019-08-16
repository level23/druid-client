<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Extractions\ExtractionInterface;

class InFilter implements FilterInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var \Level23\Druid\Extractions\ExtractionInterface|null
     */
    protected $extraction;

    /**
     * InFilter constructor.
     *
     * @param string                   $dimension
     * @param array                    $values
     * @param ExtractionInterface|null $extraction
     */
    public function __construct(string $dimension, array $values, ExtractionInterface $extraction = null)
    {
        $this->values     = $values;
        $this->dimension  = $dimension;
        $this->extraction = $extraction;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type'      => 'in',
            'dimension' => $this->dimension,
            'values'    => $this->values,
        ];

        if ($this->extraction) {
            $result['extractionFn'] = $this->extraction->toArray();
        }

        return $result;
    }
}