<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

/**
 * Class JavascriptAggregator
 *
 * Computes an arbitrary JavaScript function over a set of columns (both metrics and dimensions are allowed).
 * Your JavaScript functions are expected to return floating-point values.
 *
 * @package Level23\Druid\Aggregations
 */
class JavascriptAggregator implements AggregatorInterface
{
    protected string $outputName;

    protected array $fieldNames = [];

    protected string $fnAggregate;

    protected string $fnCombine;

    protected string $fnReset;

    /**
     * JavascriptAggregator constructor.
     *
     * @see https://druid.apache.org/docs/latest/querying/aggregations.html#javascript-aggregator
     *
     * @param array  $fieldNames
     * @param string $outputName
     * @param string $fnAggregate
     * @param string $fnCombine
     * @param string $fnReset
     */
    public function __construct(
        array $fieldNames,
        string $outputName,
        string $fnAggregate,
        string $fnCombine,
        string $fnReset
    ) {
        $this->fieldNames  = $fieldNames;
        $this->outputName  = $outputName;
        $this->fnAggregate = $fnAggregate;
        $this->fnCombine   = $fnCombine;
        $this->fnReset     = $fnReset;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'        => 'javascript',
            'name'        => $this->outputName,
            'fieldNames'  => $this->fieldNames,
            'fnAggregate' => $this->fnAggregate,
            'fnCombine'   => $this->fnCombine,
            'fnReset'     => $this->fnReset,
        ];
    }

    /**
     * Return how this aggregation will be outputted in the query results.
     *
     * @return string
     */
    public function getOutputName(): string
    {
        return $this->outputName;
    }
}