<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Types\BoundOperator;
use Level23\Druid\Extractions\ExtractionInterface;

/**
 * Class BoundFilter
 *
 * Bound filters can be used to filter on ranges of dimension values. It can be used for comparison filtering like
 * greater than, less than, greater than or equal to and less than or equal to.
 *
 * @package Level23\Druid\Filters
 */
class BoundFilter implements FilterInterface
{
    protected string $dimension;

    protected BoundOperator $operator;

    protected string $value;

    protected SortingOrder $ordering;

    protected ?ExtractionInterface $extractionFunction;

    /**
     * BoundFilter constructor.
     *
     * @param string                   $dimension         The dimension to filter on
     * @param string|BoundOperator     $operator          The operator to use. Use ">", ">=", "<", or "<=" Or use the
     *                                                    BoundOperator constants.
     * @param string                   $value             The value to compare with. This can either be a numeric or a
     *                                                    string.
     * @param string|SortingOrder|null $ordering          Specifies the sorting order using when comparing values
     *                                                    against the bound.
     * @param ExtractionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        string|BoundOperator $operator,
        string $value,
        string|SortingOrder $ordering = null,
        ?ExtractionInterface $extractionFunction = null
    ) {
        if(is_string($ordering)) {
            $ordering = SortingOrder::from(strtolower($ordering));
        }

        $this->dimension          = $dimension;
        $this->operator           = is_string($operator) ? BoundOperator::from($operator) : $operator;
        $this->value              = $value;
        $this->ordering           = $ordering ?? (is_numeric($value) ? SortingOrder::NUMERIC : SortingOrder::LEXICOGRAPHIC);
        $this->extractionFunction = $extractionFunction;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|bool|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        $result = [
            'type'      => 'bound',
            'dimension' => $this->dimension,
            'ordering'  => $this->ordering->value,
        ];

        switch ($this->operator) {
            case BoundOperator::GE:
                $result['lower']       = $this->value;
                $result['lowerStrict'] = false;
                break;
            case BoundOperator::GT:
                $result['lower']       = $this->value;
                $result['lowerStrict'] = true;
                break;
            case BoundOperator::LE:
                $result['upper']       = $this->value;
                $result['upperStrict'] = false;
                break;
            case BoundOperator::LT:
                $result['upper']       = $this->value;
                $result['upperStrict'] = true;
                break;
        }

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->toArray();
        }

        return $result;
    }
}