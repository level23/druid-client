<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Extractions\ExtractionInterface;

class SelectorFilter implements FilterInterface
{
    protected string $dimension;

    protected ?string $value;

    protected ?ExtractionInterface $extractionFunction;

    /**
     * InFilter constructor.
     *
     * @param string                   $dimension
     * @param string|null              $value
     * @param ExtractionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        ?string $value,
        ?ExtractionInterface $extractionFunction = null
    ) {
        $this->value              = $value;
        $this->dimension          = $dimension;
        $this->extractionFunction = $extractionFunction;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|null|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        $result = [
            'type'      => 'selector',
            'dimension' => $this->dimension,
            'value'     => $this->value,
        ];

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->toArray();
        }

        return $result;
    }
}