<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class ThetaSketchEstimatePostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected PostAggregatorInterface $field;

    protected ?int $errorBoundsStdDev;

    /**
     * ThetaSketchEstimatePostAggregator constructor.
     *
     * @param PostAggregatorInterface $field             Post aggregator that refers to a theta sketch (fieldAccess or
     *                                                   a thetaSketchSetOp post aggregator)
     * @param string                  $outputName        The name as it will be used in our result.
     * @param int|null                $errorBoundsStdDev When given, the estimate is returned with error bounds for
     *                                                   this number of standard deviations (1, 2 or 3).
     */
    public function __construct(PostAggregatorInterface $field, string $outputName, ?int $errorBoundsStdDev = null)
    {
        $this->outputName        = $outputName;
        $this->field             = $field;
        $this->errorBoundsStdDev = $errorBoundsStdDev;
    }

    /**
     * Return the post aggregator as it can be used in a druid query.
     *
     * @return array<string,string|int|array<mixed>>
     */
    public function toArray(): array
    {
        $result = [
            'type'  => 'thetaSketchEstimate',
            'name'  => $this->outputName,
            'field' => $this->field->toArray(),
        ];

        if ($this->errorBoundsStdDev !== null) {
            $result['errorBoundsStdDev'] = $this->errorBoundsStdDev;
        }

        return $result;
    }
}
