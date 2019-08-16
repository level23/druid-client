<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class LookupExtraction implements ExtractionInterface
{
    /**
     * @var string
     */
    protected $lookupName;

    /**
     * @var bool
     */
    protected $retainMissingValue;

    /**
     * @var string|null
     */
    protected $replaceMissingValueWith;

    /**
     * @var bool|null
     */
    protected $injective;

    /**
     * @var bool|null
     */
    protected $optimize;

    /**
     * LookupExtraction constructor.
     *
     * @param string      $lookupName
     * @param bool|string $replaceMissingValue When true, we will keep values which are not known in the lookup function. The
     *                                 original value will be kept. If false, the missing items will not be kept in the
     *                                 result set. If this is a string, we will keep the missing values and replace them
     *                                 with the string value.
     * @param bool        $optimize
     * @param bool|null   $injective           A property of injective can override the lookup's own sense of whether or not it
     *                                 is
     *                                 injective. If left unspecified, Druid will use the registered
     *                                 cluster-wide lookup configuration.
     *
     * For  more information about injective, see:
     * https://druid.apache.org/docs/latest/querying/lookups.html#query-execution
     */
    public function __construct(
        string $lookupName,
        $replaceMissingValue = true,
        bool $optimize = true,
        ?bool $injective = null

    ) {
        $this->lookupName              = $lookupName;
        $this->retainMissingValue      = is_string($replaceMissingValue) ? true : $replaceMissingValue;
        $this->replaceMissingValueWith = is_string($replaceMissingValue) ? $replaceMissingValue : null;
        $this->injective               = $injective;
        $this->optimize                = $optimize;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type'     => 'registeredLookup',
            'lookup'   => $this->lookupName,
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