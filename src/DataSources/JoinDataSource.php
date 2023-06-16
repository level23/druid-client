<?php
declare(strict_types=1);

namespace Level23\Druid\DataSources;

use Level23\Druid\Types\JoinType;

class JoinDataSource implements DataSourceInterface
{
    protected DataSourceInterface $left;

    protected DataSourceInterface $right;

    protected string $rightPrefix;

    protected string $condition;

    protected JoinType $joinType;

    /**
     * @param \Level23\Druid\DataSources\DataSourceInterface $left
     * @param \Level23\Druid\DataSources\DataSourceInterface $right
     * @param string                                         $rightPrefix
     * @param string                                         $condition
     * @param string|JoinType                                         $joinType
     */
    public function __construct(
        DataSourceInterface $left,
        DataSourceInterface $right,
        string $rightPrefix,
        string $condition,
        string|JoinType $joinType
    ) {
        $this->left        = $left;
        $this->right       = $right;
        $this->rightPrefix = $rightPrefix;
        $this->condition   = $condition;
        $this->joinType    = is_string($joinType) ? JoinType::from(strtoupper($joinType)) : $joinType;
    }

    /**
     * Return the TableDataSource so that it can be used in a druid query.
     *
     * @return array<string,string|array<string,string|string[]|array<mixed>>>
     */
    public function toArray(): array
    {
        return [
            'type'        => 'join',
            'left'        => $this->left->toArray(),
            'right'       => $this->right->toArray(),
            'rightPrefix' => $this->rightPrefix,
            'condition'   => $this->condition,
            'joinType'    => $this->joinType->value,
        ];
    }
}