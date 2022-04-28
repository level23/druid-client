<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class FieldAccessPostAggregator implements PostAggregatorInterface
{
    protected string $fieldName;

    protected string $outputName;

    protected bool $finalizing;

    public function __construct(string $fieldName, string $outputName, bool $finalizing = false)
    {
        $this->fieldName  = $fieldName;
        $this->outputName = $outputName;
        $this->finalizing = $finalizing;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'type'      => ($this->finalizing ? 'finalizingFieldAccess' : 'fieldAccess'),
            'name'      => $this->outputName,
            'fieldName' => $this->fieldName,
        ];
    }
}