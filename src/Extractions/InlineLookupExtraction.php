<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class InlineLookupExtraction implements ExtractionInterface
{
    /**
     * @var array
     */
    protected $map;

    /**
     * @var bool
     */
    protected $optimize;

    /**
     * @var bool|null
     */
    protected $injective;

    /**
     * @var bool
     */
    protected $retainMissingValue;

    /**
     * @var string|null
     */
    private $replaceMissingValueWith;

    /**
     * InlineLookupExtraction constructor.
     *
     * @param array       $map                 A map with items. The key is the value of the given dimension. It will
     *                                         be replaced by the value.
     * @param bool|string $replaceMissingValue When true, we will keep values which are not known in the lookup
     *                                         function. The original value will be kept. If false, the missing items
     *                                         will not be kept in the result set. If this is a string, we will keep
     *                                         the missing values and replace them with the string value.
     * @param bool        $optimize            When set to true, we allow the optimization layer (which will run on the
     *                                         broker) to rewrite the extraction filter if needed.
     * @param bool|null   $injective           A property of injective can override the lookup's own sense of whether
     *                                         or not it is injective. If left unspecified, Druid will use the
     *                                         registered cluster-wide lookup configuration.
     */
    public function __construct(array $map, $replaceMissingValue = false, bool $optimize = true, bool $injective = null)
    {
        $this->map                     = $map;
        $this->retainMissingValue      = is_string($replaceMissingValue) ? true : (bool)$replaceMissingValue;
        $this->replaceMissingValueWith = is_string($replaceMissingValue) ? $replaceMissingValue : null;
        $this->optimize                = $optimize;
        $this->injective               = $injective;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
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