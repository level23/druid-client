<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

use InvalidArgumentException;
use Level23\Druid\Collections\PostAggregationCollection;

abstract class MethodPostAggregator implements PostAggregatorInterface
{
    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var \Level23\Druid\Collections\PostAggregationCollection
     */
    protected $fields;

    /**
     * @var string
     */
    protected $type;

    /**
     *  constructor.
     *
     * @param string                    $outputName
     * @param PostAggregationCollection $fields
     * @param string                    $type
     */
    public function __construct(string $outputName, PostAggregationCollection $fields, string $type = 'long')
    {
        $type = strtolower($type);
        if (!in_array($type, ['long', 'double'])) {
            throw new InvalidArgumentException(
                'Supported types are "long" and "double". Value given: ' . $type
            );
        }
        $this->outputName = $outputName;
        $this->fields     = $fields;
        $this->type       = $type;
    }

    /**
     * Return the post aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'   => $this->type . ucfirst($this->getMethod()),
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