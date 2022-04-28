<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

use Level23\Druid\Collections\PostAggregationCollection;

class JavaScriptPostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected PostAggregationCollection $fields;

    protected string $javascriptFunction;

    /**
     * JavaScriptPostAggregator constructor.
     *
     * NOTE: JavaScript-based functionality is disabled by default. Please refer to the Druid JavaScript programming
     * guide for guidelines about using Druid's JavaScript functionality, including instructions on how to enable it.
     *
     * @param string                    $outputName
     * @param PostAggregationCollection $fields
     * @param string                    $javascriptFunction
     */
    public function __construct(string $outputName, PostAggregationCollection $fields, string $javascriptFunction)
    {
        $this->outputName         = $outputName;
        $this->fields             = $fields;
        $this->javascriptFunction = $javascriptFunction;
    }

    /**
     * Return the post aggregator as it can be used in a druid query.
     *
     * @return array<string,string|array<array<string,string|array<mixed>>>>
     */
    public function toArray(): array
    {
        return [
            'type'       => 'javascript',
            'name'       => $this->outputName,
            'fieldNames' => $this->fields->toArray(),
            'function'   => $this->javascriptFunction,
        ];
    }
}