<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Types\JoinType;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\DataSources\JoinDataSource;
use Level23\Druid\DataSources\TableDataSource;
use Level23\Druid\DataSources\QueryDataSource;
use Level23\Druid\DataSources\UnionDataSource;
use Level23\Druid\DataSources\DataSourceInterface;

trait HasDataSource
{
    protected DruidClient $client;

    protected DataSourceInterface $dataSource;

    /**
     * Alias method from "dataSource"
     *
     * @param string|DataSourceInterface $dataSource
     *
     * @return self
     */
    public function from($dataSource): self
    {
        return $this->dataSource($dataSource);
    }

    /**
     * Update/set the dataSource
     *
     * @param string|DataSourceInterface $dataSource
     *
     * @return self
     */
    public function dataSource($dataSource): self
    {
        if (is_string($dataSource)) {
            $this->dataSource = new TableDataSource($dataSource);
        } else {
            $this->dataSource = $dataSource;
        }

        return $this;
    }

    /**
     * @param string|DataSourceInterface|\Closure $dataSourceOrClosure
     * @param string                              $as
     * @param string                              $condition
     * @param string                              $joinType
     *
     * @return self
     */
    public function join($dataSourceOrClosure, string $as, string $condition, string $joinType = JoinType::INNER): self
    {
        if ($this->dataSource instanceof TableDataSource && $this->dataSource->dataSourceName == '') {
            throw new InvalidArgumentException('You first have to define your "from" dataSource before you can join!');
        }

        if (substr($as, -1) != '.') {
            $as .= '.';
        }

        $this->dataSource = new JoinDataSource(
            $this->dataSource,
            $this->getDataSource($dataSourceOrClosure),
            $as,
            $condition,
            $joinType
        );

        return $this;
    }

    /**
     * @param string|DataSourceInterface|\Closure $dataSourceOrClosure
     * @param string                              $as
     * @param string                              $condition
     *
     * @return self
     */
    public function leftJoin($dataSourceOrClosure, string $as, string $condition): self
    {
        return $this->join($dataSourceOrClosure, $as, $condition, JoinType::LEFT);
    }

    /**
     * @param string|DataSourceInterface|\Closure $dataSourceOrClosure
     * @param string                              $as
     * @param string                              $condition
     *
     * @return self
     */
    public function innerJoin($dataSourceOrClosure, string $as, string $condition): self
    {
        return $this->join($dataSourceOrClosure, $as, $condition, JoinType::INNER);
    }

    /**
     * Unions allow you to treat two or more tables as a single datasource.
     *
     * With the native union datasource, the tables do not need to have identical schemas. If they do not
     * fully match up, then columns that exist in one table but not another will be treated as if they contained all
     * null values in the tables where they do not exist.
     *
     * @param string|string[] $dataSources
     *
     * @return $this
     * @see https://druid.apache.org/docs/latest/querying/datasource.html#union
     */
    public function union($dataSources): self
    {
        if (!$this->dataSource instanceof TableDataSource) {
            throw new InvalidArgumentException('We can only union an table dataSource! You currently are using a ' . get_class($this->dataSource));
        }

        $dataSources   = (array)$dataSources;
        $dataSources[] = $this->dataSource->dataSourceName;

        $this->dataSource = new UnionDataSource($dataSources);

        return $this;
    }

    /**
     * @param string|DataSourceInterface|\Closure $dataSourceOrClosure
     *
     * @return DataSourceInterface
     * @throws InvalidArgumentException
     */
    protected function getDataSource($dataSourceOrClosure)
    {
        if (is_string($dataSourceOrClosure)) {
            return new TableDataSource($dataSourceOrClosure);
        } elseif ($dataSourceOrClosure instanceof DataSourceInterface) {
            return $dataSourceOrClosure;
        } elseif ($dataSourceOrClosure instanceof Closure) {

            $builder = new QueryBuilder($this->client);
            call_user_func($dataSourceOrClosure, $builder);

            return new QueryDataSource($builder->getQuery());
        } else {
            throw new InvalidArgumentException(
                'Invalid dataSource given! This can either be a string (dataSource name),  ' .
                'an object which implements the DataSourceInterface, or a Closure function which allows ' .
                'you to build a sub-query.'
            );
        }
    }
}