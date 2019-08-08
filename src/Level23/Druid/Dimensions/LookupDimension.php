<?php

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
     * @var bool
     */
    protected $replaceMissingValue;

    /**
     * @var string|null
     */
    protected $replaceMissingValueWith;

    /**
     * DefaultDimension constructor.
     *
     * A property of retainMissingValue and replaceMissingValueWith can be specified at query
     * time to hint how to handle missing values. Setting replaceMissingValueWith to "" has the
     * same effect as setting it to null or omitting the property. Setting retainMissingValue to true
     * will use the dimension's original value if it is not found in the lookup. The default values are
     * replaceMissingValueWith = null and retainMissingValue = false which causes missing values to be
     * treated as missing.
     *
     * It is illegal to set retainMissingValue = true and also specify a replaceMissingValueWith.
     *
     * @param string      $dimension
     * @param string      $registeredLookupFunction
     * @param string      $outputName
     * @param bool        $replaceMissingValue
     * @param string|null $replaceMissingValueWith
     */
    public function __construct(
        string $dimension,
        string $registeredLookupFunction,
        string $outputName = '',
        bool $replaceMissingValue = false,
        ?string $replaceMissingValueWith = null
    ) {
        $this->dimension                = $dimension;
        $this->outputName               = $outputName ?: $dimension;
        $this->registeredLookupFunction = $registeredLookupFunction;
        $this->replaceMissingValue      = $replaceMissingValue;
        $this->replaceMissingValueWith  = $replaceMissingValueWith;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array
     */
    public function getDimension(): array
    {
        $result = [
            'type'       => 'lookup',
            'dimension'  => $this->dimension,
            'outputName' => $this->outputName,
            'name'       => $this->registeredLookupFunction,
        ];

        if (!empty($this->replaceMissingValueWith)) {
            $result['replaceMissingValueWith'] = $this->replaceMissingValueWith;
        } elseif ($this->replaceMissingValue) {
            $result['replaceMissingValue'] = $this->replaceMissingValue;
        }

        return $result;
    }
}