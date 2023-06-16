<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

use InvalidArgumentException;
use Level23\Druid\Types\DataType;
use Level23\Druid\Collections\PostAggregationCollection;

abstract class MethodPostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected PostAggregationCollection $fields;

    protected DataType $type;

    /**
     *  constructor.
     *
     * @param string                    $outputName
     * @param PostAggregationCollection $fields
     * @param string|DataType                    $type
     */
    public function __construct(string $outputName, PostAggregationCollection $fields, string|DataType $type = DataType::LONG)
    {
        $type = is_string($type)? DataType::from(strtolower($type)) : $type;
        if (!in_array($type, [DataType::LONG, DataType::DOUBLE])) {
            throw new InvalidArgumentException(
                'Supported types are "long" and "double". Value given: ' . $type->value
            );
        }
        $this->outputName = $outputName;
        $this->fields     = $fields;
        $this->type       = $type;
    }

    /**
     * Return the post aggregator as it can be used in a druid query.
     *
     * @return array<string,string|array<array<string,string|array<mixed>>>>
     */
    public function toArray(): array
    {
        return [
            'type'   => $this->type->value . ucfirst($this->getMethod()),
            'name'   => $this->outputName,
            'fields' => $this->fields->toArray(),
        ];
    }

    /**
     * Returns the method for the type aggregation
     *
     * @return string
     */
    protected abstract function getMethod(): string;
}