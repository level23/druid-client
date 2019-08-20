<?php

declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\VirtualColumns\VirtualColumn;

trait HasVirtualColumns
{
    /**
     * @var array|\Level23\Druid\VirtualColumns\VirtualColumnInterface[]
     */
    protected $virtualColumns = [];

    /**
     * Create a virtual column.
     *
     * Virtual columns are queryable column "views" created from a set of columns during a query.
     *
     * A virtual column can potentially draw from multiple underlying columns, although a virtual column always
     * presents itself as a single column.
     *
     * Virtual columns can be used as dimensions or as inputs to aggregators.
     *
     * @param string $expression
     * @param string $as
     * @param string $outputType
     *
     * @return $this
     * @see https://druid.apache.org/docs/latest/misc/math-expr.html
     */
    public function virtualColumn(string $expression, string $as, $outputType = 'string')
    {
        $this->virtualColumns[] = new VirtualColumn($as, $expression, $outputType);

        return $this;
    }
}