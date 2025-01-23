<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

class TopNQueryContext extends QueryContext implements ContextInterface
{
    /**
     * The top minTopNThreshold local results from each segment are returned for merging to determine the global topN.
     *
     * Default: 1000
     *
     * @param int $minTopNThreshold
     *
     * @return $this
     */
    public function setMinTopNThreshold(int $minTopNThreshold): self
    {
        $this->properties['minTopNThreshold'] = $minTopNThreshold;

        return $this;
    }
}