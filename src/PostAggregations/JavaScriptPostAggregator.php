<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class JavaScriptPostAggregator implements PostAggregatorInterface
{
    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var array|string[]
     */
    protected $fields;

    /**
     * @var string
     */
    protected $javascriptFunction;

    /**
     * JavaScriptPostAggregator constructor.
     *
     * NOTE: JavaScript-based functionality is disabled by default. Please refer to the Druid JavaScript programming
     * guide for guidelines about using Druid's JavaScript functionality, including instructions on how to enable it.
     *
     * @param string         $outputName
     * @param array|string[] $fields
     * @param string         $javascriptFunction
     */
    public function __construct(string $outputName, array $fields, string $javascriptFunction)
    {
        $this->outputName         = $outputName;
        $this->fields             = $fields;
        $this->javascriptFunction = $javascriptFunction;
    }

    /**
     * Return the post aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'       => 'javascript',
            'name'       => $this->outputName,
            'fieldNames' => $this->fields,
            'function'   => $this->javascriptFunction,
        ];
    }
}