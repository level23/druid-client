<?php
declare(strict_types=1);

namespace Level23\Druid\Metadata;

class Structure
{
    /**
     * @var string
     */
    public $dataSource;

    /**
     * A list of the dimensions and their associated type.
     *
     * @var array
     */
    public $dimensions = [];

    /**
     * A list of the metrics and their associated type.
     *
     * @var array
     */
    public $metrics = [];

    public function __construct(string $dataSource, array $dimensions, array $metrics)
    {
        $this->dataSource = $dataSource;
        $this->dimensions = $dimensions;
        $this->metrics    = $metrics;
    }
}