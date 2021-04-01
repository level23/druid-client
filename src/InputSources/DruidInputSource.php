<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

use Level23\Druid\Interval\IntervalInterface;

class DruidInputSource implements InputSourceInterface
{
    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var \Level23\Druid\Interval\IntervalInterface
     */
    protected $interval;

    /**
     * DruidInputSource constructor.
     *
     * @param string                                    $dataSource
     * @param \Level23\Druid\Interval\IntervalInterface $interval
     */
    public function __construct(string $dataSource, IntervalInterface $interval)
    {
        $this->dataSource = $dataSource;
        $this->interval   = $interval;
    }

    public function toArray(): array
    {
        return [
            'type'       => 'druid',
            'dataSource' => $this->dataSource,
            'interval'   => $this->interval->getInterval(),
        ];
    }
}