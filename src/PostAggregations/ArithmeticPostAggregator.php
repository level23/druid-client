<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

use Level23\Druid\Types\ArithmeticFunction;

class ArithmeticPostAggregator implements PostAggregatorInterface
{
    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var \Level23\Druid\Types\ArithmeticFunction|string
     */
    protected $function;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var bool
     */
    protected $floatingPointOrdering;

    /**
     * ArithmeticPostAggregator constructor.
     *
     * The arithmetic post-aggregator applies the provided function to the given fields from left to right. The fields
     * can be aggregators or other post aggregators.
     *
     * Notes:
     * -  / division always returns 0 if dividing by 0, regardless of the numerator.
     * - quotient division behaves like regular floating point division
     *
     * @param string                    $outputName
     * @param string|ArithmeticFunction $function              Supported functions are +, -, *, /, and quotient.
     * @param array|string[]            $fields                List with field names which are used for this function
     * @param bool                      $floatingPointOrdering By default floating point ordering is used. When set to
     *                                                         false we will use numericFirst ordering. It returns
     *                                                         finite values first, followed by NaN, and infinite
     *                                                         values last.
     */
    public function __construct(string $outputName, $function, array $fields, bool $floatingPointOrdering = true)
    {
        $this->outputName            = $outputName;
        $this->function              = ArithmeticFunction::validate($function);
        $this->fields                = $fields;
        $this->floatingPointOrdering = $floatingPointOrdering;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'     => 'arithmetic',
            'name'     => $this->outputName,
            'fn'       => $this->function,
            'fields'   => $this->fields,
            'ordering' => $this->floatingPointOrdering ? null : 'numericFirst',
        ];
    }
}