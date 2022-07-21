<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class InlineLookupExtraction implements ExtractionInterface
{
    /**
     * @var array<string,string>
     */
    protected array $map;

    protected bool $optimize;

    protected ?bool $injective = null;

    protected bool $retainMissingValue;

    private ?string $replaceMissingValueWith = null;

    /**
     * InlineLookupExtraction constructor.
     *
     * @param array<string,string> $map              A map with items. The key is the value of the given dimension. It
     *                                               will be replaced by the value.
     * @param bool|string          $keepMissingValue When true, we will keep values which are not known in the lookup
     *                                               function. The original value will be kept. If false, the missing
     *                                               items will not be kept in the result set. If this is a string, we
     *                                               will keep the missing values and replace them with the string
     *                                               value.
     * @param bool                 $optimize         When set to true, we allow the optimization layer (which will run
     *                                               on the broker) to rewrite the extraction filter if needed.
     * @param bool|null            $injective        A property of injective can override the lookups own sense of
     *                                               whether or not it is injective. If left unspecified, Druid will
     *                                               use the registered cluster-wide lookup configuration.
     */
    public function __construct(array $map, $keepMissingValue = false, bool $optimize = true, bool $injective = null)
    {
        $this->map       = $map;
        $this->optimize  = $optimize;
        $this->injective = $injective;

        if (is_string($keepMissingValue)) {
            $this->replaceMissingValueWith = $keepMissingValue;
        } else {
            $this->retainMissingValue = (bool)$keepMissingValue;
        }
    }

    /**
     * Return the Extraction Function, so it can be used in a druid query.
     *
     * @return array<string,string|bool|array<mixed>>
     */
    public function toArray(): array
    {
        $result = [
            'type'     => 'lookup',
            'lookup'   => [
                'type' => 'map',
                'map'  => $this->map,
            ],
            'optimize' => $this->optimize,
        ];

        if ($this->injective !== null) {
            $result['injective'] = $this->injective;
        }

        if ($this->replaceMissingValueWith !== null) {
            $result['replaceMissingValueWith'] = $this->replaceMissingValueWith;
        } elseif ($this->retainMissingValue) {
            $result['retainMissingValue'] = $this->retainMissingValue;
        }

        return $result;
    }
}