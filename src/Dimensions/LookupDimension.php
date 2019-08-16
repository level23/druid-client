<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

class LookupDimension implements DimensionInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var string
     */
    protected $registeredLookupFunction;

    /**
     * @var bool|string
     */
    protected $replaceMissingValue;

    /**
     * DefaultDimension constructor.
     *
     * A property of retainMissingValue and replaceMissingValueWith can be specified at query
     * time to hint how to handle missing values. Setting replaceMissingValueWith to "" has the
     * same effect as setting it to null or omitting the property. Setting replaceMissingValue to
     * true will use the dimension's original value if it is not found in the lookup. If you set
     * it to a string it wil replace the missing value with that string. It defaults to false
     * which will ignore the value.
     *
     * @param string      $dimension
     * @param string      $registeredLookupFunction
     * @param string      $outputName
     * @param bool|string $replaceMissingValue
     */
    public function __construct(
        string $dimension,
        string $registeredLookupFunction,
        string $outputName = '',
        $replaceMissingValue = false
    ) {
        $this->dimension                = $dimension;
        $this->outputName               = $outputName ?: $dimension;
        $this->registeredLookupFunction = $registeredLookupFunction;
        $this->replaceMissingValue      = $replaceMissingValue;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type'       => 'lookup',
            'dimension'  => $this->dimension,
            'outputName' => $this->outputName,
            'name'       => $this->registeredLookupFunction,
        ];

        if ($this->replaceMissingValue === true) {
            $result['retainMissingValue'] = true;
        } elseif (is_string($this->replaceMissingValue)) {
            $result['replaceMissingValueWith'] = $this->replaceMissingValue;
        }

        return $result;
    }

    /**
     * Return the name of the dimension which is selected.
     *
     * @return string
     */
    public function getDimension(): string
    {
        return $this->dimension;
    }

    /**
     * Return the output name of this dimension
     *
     * @return string
     */
    public function getOutputName(): string
    {
        return $this->outputName;
    }
}