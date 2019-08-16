<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

class TopNQueryContext extends QueryContext implements ContextInterface
{
    /**
     * The top minTopNThreshold local results from each segment are returned for merging to determine the global topN.
     *
     * Default: 1000
     * @var int
     */
    public $minTopNThreshold;


    /**
     * Return the context as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = parent::toArray();

        if( $this->minTopNThreshold !== null ) {
            $result['minTopNThreshold'] = $this->minTopNThreshold;
        }

        return $result;
    }
}