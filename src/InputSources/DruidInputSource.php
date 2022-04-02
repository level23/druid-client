<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

use InvalidArgumentException;
use Level23\Druid\Concerns\HasFilter;
use Level23\Druid\Concerns\HasInterval;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Interval\IntervalInterface;

class DruidInputSource implements InputSourceInterface
{
    use HasFilter, HasInterval;

    protected string $dataSource;

    protected ?IntervalInterface $interval = null;

    /**
     * @return \Level23\Druid\Interval\IntervalInterface|null
     */
    public function getInterval(): ?IntervalInterface
    {
        return $this->interval;
    }

    /**
     * @param \Level23\Druid\Interval\IntervalInterface|null $interval
     *
     * @return \Level23\Druid\InputSources\DruidInputSource
     */
    public function setInterval(?IntervalInterface $interval): DruidInputSource
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * DruidInputSource constructor.
     *
     * @param string                                         $dataSource
     * @param \Level23\Druid\Interval\IntervalInterface|null $interval
     * @param \Level23\Druid\Filters\FilterInterface|null    $filter
     */
    public function __construct(string $dataSource, IntervalInterface $interval = null, FilterInterface $filter = null)
    {
        $this->dataSource = $dataSource;
        $this->interval   = $interval;
        $this->filter     = $filter;
    }

    public function toArray(): array
    {
        if (empty($this->interval)) {
            throw new InvalidArgumentException('You have to specify the interval which you want to use for your query!');
        }

        $response = [
            'type'       => 'druid',
            'dataSource' => $this->dataSource,
            'interval'   => $this->interval->getInterval(),
        ];

        if (!empty($this->filter)) {
            $response['filter'] = $this->filter->toArray();
        }

        return $response;
    }
}