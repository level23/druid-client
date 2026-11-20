<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

use Level23\Druid\Types\ThetaSketchFunction;
use Level23\Druid\Collections\PostAggregationCollection;

class ThetaSketchSetOpPostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected ThetaSketchFunction $function;

    protected PostAggregationCollection $fields;

    protected ?int $size;

    /**
     * ThetaSketchSetOpPostAggregator constructor.
     *
     * @param string                     $outputName The name as it will be used in our result.
     * @param string|ThetaSketchFunction $function   The set operation: UNION, INTERSECT or NOT.
     * @param PostAggregationCollection  $fields     Post aggregators that refer to theta sketches.
     * @param int|null                   $size       Sketch size, a power of 2. Druid uses 16384 when not given.
     */
    public function __construct(
        string $outputName,
        string|ThetaSketchFunction $function,
        PostAggregationCollection $fields,
        ?int $size = null
    ) {
        $this->outputName = $outputName;
        $this->function   = is_string($function) ? ThetaSketchFunction::from(strtoupper($function)) : $function;
        $this->fields     = $fields;
        $this->size       = $size;
    }

    /**
     * Return the post aggregator as it can be used in a druid query.
     *
     * @return array<string,string|int|array<mixed>>
     */
    public function toArray(): array
    {
        $result = [
            'type'   => 'thetaSketchSetOp',
            'name'   => $this->outputName,
            'func'   => $this->function->value,
            'fields' => $this->fields->toArray(),
        ];

        if ($this->size !== null) {
            $result['size'] = $this->size;
        }

        return $result;
    }
}
