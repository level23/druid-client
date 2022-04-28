<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

class LookupDimension implements DimensionInterface
{
    protected string $dimension;

    protected string $outputName;

    protected string $registeredLookupFunction;

    /**
     * @var bool|string
     */
    protected $keepMissingValue;

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
     * @param bool|string $keepMissingValue
     */
    public function __construct(
        string $dimension,
        string $registeredLookupFunction,
        string $outputName = '',
        $keepMissingValue = false
    ) {
        $this->dimension                = $dimension;
        $this->outputName               = $outputName ?: $dimension;
        $this->registeredLookupFunction = $registeredLookupFunction;
        $this->keepMissingValue         = $keepMissingValue;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array<string,string|bool>
     */
    public function toArray(): array
    {
        $result = [
            'type'       => 'lookup',
            'dimension'  => $this->dimension,
            'outputName' => $this->outputName,
            'name'       => $this->registeredLookupFunction,
        ];

        if ($this->keepMissingValue === true) {
            $result['retainMissingValue'] = true;
        } elseif (is_string($this->keepMissingValue)) {
            $result['replaceMissingValueWith'] = $this->keepMissingValue;
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