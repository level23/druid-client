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
use Level23\Druid\DataSources\LookupDataSource;
use Level23\Druid\DataSources\InlineDataSource;
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
     * Join a lookup dataSource. Lookup datasources correspond to Druid's key-value lookup objects.
     *
     * Lookup datasources are key-value oriented and always have exactly two columns: k (the key) and v (the value),
     * and both are always strings.
     *
     * @param string $lookupName The name of the lookup dataSource.
     * @param string $as         The alias name as the dataSource will be used in the query.
     * @param string $condition  The condition how the match will be made.
     * @param string $joinType   The join type to use. This can be INNER or LEFT.
     *
     * @return self
     */
    public function joinLookup(
        string $lookupName,
        string $as,
        string $condition,
        string $joinType = JoinType::INNER
    ): self {
        $lookupDataSource = new LookupDataSource($lookupName);

        return $this->join($lookupDataSource, $as, $condition, $joinType);
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
     * Inline datasources allow you to query a small amount of data that is embedded in the query itself.
     * They are useful when you want to write a query on a small amount of data without loading it first.
     * They are also useful as inputs into a join.
     *
     * Each row is an array that must be exactly as long as the list of columnNames. The first element in
     * each row corresponds to the first column in columnNames, and so on.
     *
     * @param string[]        $columnNames
     * @param array<scalar[]> $rows
     */
    public function fromInline(array $columnNames, array $rows): self
    {
        $this->dataSource = new InlineDataSource($columnNames, $rows);

        return $this;
    }

    /**
     * Unions allow you to treat two or more tables as a single datasource.
     *
     * With the native union datasource, the tables do not need to have identical schemas. If they do not
     * fully match up, then columns that exist in one table but not another will be treated as if they contained all
     * null values in the tables where they do not exist.
     *
     * @param string|string[] $dataSources
     * @param bool            $append When true, we will append the current used dataSource in the union.
     *
     * @return $this
     * @see https://druid.apache.org/docs/latest/querying/datasource.html#union
     */
    public function union($dataSources, bool $append = true): self
    {
        $dataSources = (array)$dataSources;

        if ($append) {
            if (!$this->dataSource instanceof TableDataSource) {
                throw new InvalidArgumentException('We can only union an table dataSource! You currently are using a ' . get_class($this->dataSource));
            }

            $dataSources[] = $this->dataSource->dataSourceName;
        }

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