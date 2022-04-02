<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

class ScanQueryContext extends QueryContext
{
    /**
     * The maximum number of rows returned when time ordering is used. Overrides the identically named config.
     * Default: druid.query.scan.maxRowsQueuedForOrdering
     *
     * @param int $maxRowsQueuedForOrdering
     *
     * @return $this
     */
    public function setMaxRowsQueuedForOrdering(int $maxRowsQueuedForOrdering): self
    {
        $this->properties['maxRowsQueuedForOrdering'] = $maxRowsQueuedForOrdering;

        return $this;
    }

    /**
     * The maximum number of segments scanned per historical when time ordering is used. Overrides the identically
     * named config.
     * Default: druid.query.scan.maxSegmentPartitionsOrderedInMemory
     *
     * @param int $maxSegmentPartitionsOrderedInMemory
     *
     * @return $this
     */
    public function setMaxSegmentPartitionsOrderedInMemory(int $maxSegmentPartitionsOrderedInMemory): self
    {
        $this->properties['maxSegmentPartitionsOrderedInMemory'] = $maxSegmentPartitionsOrderedInMemory;

        return $this;
    }
}