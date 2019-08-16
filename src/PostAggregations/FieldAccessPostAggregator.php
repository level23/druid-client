<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class FieldAccessPostAggregator implements PostAggregatorInterface
{
    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var bool
     */
    protected $finalizing;

    public function __construct(string $fieldName, string $outputName, bool $finalizing = false)
    {
        $this->fieldName  = $fieldName;
        $this->outputName = $outputName;
        $this->finalizing = $finalizing;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
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