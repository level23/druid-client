<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

class LookupDimension implements DimensionInterface
{
    protected string $dimension;

    protected string $outputName;

    /**
     * @var string|array<int|string,string>
     */
    protected string|array $registeredLookupFunctionOrMap;

    /**
     * @var bool|string
     */
    protected string|bool $keepMissingValue;

    protected bool $isOneToOne;

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
     * @param string                          $dimension
     * @param string|array<int|string,string> $registeredLookupFunctionOrMap
     * @param string                          $outputName
     * @param bool|string                     $keepMissingValue
     * @param bool                            $isOneToOne
     */
    public function __construct(
        string $dimension,
        array|string $registeredLookupFunctionOrMap,
        string $outputName = '',
        bool|string $keepMissingValue = false,
        bool $isOneToOne = false
    ) {
        $this->dimension                     = $dimension;
        $this->outputName                    = $outputName ?: $dimension;
        $this->registeredLookupFunctionOrMap = $registeredLookupFunctionOrMap;
        $this->keepMissingValue              = $keepMissingValue;
        $this->isOneToOne                    = $isOneToOne;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array<string,string|bool|array<string,string|bool|string[]>>
     */
    public function toArray(): array
    {
        $result = [
            'type'       => 'lookup',
            'dimension'  => $this->dimension,
            'outputName' => $this->outputName,
        ];

        if (is_string($this->registeredLookupFunctionOrMap)) {
            $result['name'] = $this->registeredLookupFunctionOrMap;
        } elseif (is_array($this->registeredLookupFunctionOrMap)) {
            $result['lookup'] = [
                'type'       => 'map',
                'map'        => $this->registeredLookupFunctionOrMap,
                'isOneToOne' => $this->isOneToOne,
            ];
        }

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