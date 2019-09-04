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
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string|null
     */
    protected $ordering;

    /**
     * @var \Level23\Druid\Extractions\ExtractionInterface|null
     */
    protected $extractionFunction;

    /**
     * BoundFilter constructor.
     *
     * @param string                   $dimension         The dimension to filter on
     * @param string                   $operator          The operator to use. Use ">", ">=", "<", or "<=" Or use the
     *                                                    BoundOperator constants.
     * @param string                   $value             The value to compare with. This can either be an numeric or a
     *                                                    string.
     * @param string|null              $ordering          Specifies the sorting order to use when comparing values
     *                                                    against the bound.
     * @param ExtractionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        string $operator,
        string $value,
        string $ordering = null,
        ExtractionInterface $extractionFunction = null
    ) {
        $this->dimension          = $dimension;
        $this->operator           = BoundOperator::validate($operator);
        $this->value              = $value;
        $this->ordering           = $ordering ?: (is_numeric($value) ? SortingOrder::NUMERIC : SortingOrder::LEXICOGRAPHIC);
        $this->extractionFunction = $extractionFunction;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type'      => 'bound',
            'dimension' => $this->dimension,
            'ordering'  => $this->ordering,
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