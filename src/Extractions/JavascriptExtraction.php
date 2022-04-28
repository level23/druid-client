<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class JavascriptExtraction implements ExtractionInterface
{
    protected string $javascript;

    protected bool $injective;

    /**
     * JavascriptExtraction constructor.
     *
     * @param string $javascript A javascript function which will receive the dimension/value. The function can then
     *                           extract the needed value from it and should return it.
     * @param bool   $injective  A property of injective specifies if the javascript function preserves uniqueness. The
     *                           default value is false meaning uniqueness is not preserved
     */
    public function __construct(string $javascript, bool $injective = false)
    {
        $this->javascript = $javascript;
        $this->injective  = $injective;
    }

    /**
     * Return the Extraction Function, so it can be used in a druid query.
     *
     * @return array<string,string|bool>
     */
    public function toArray(): array
    {
        return [
            'type'      => 'javascript',
            'function'  => $this->javascript,
            'injective' => $this->injective,
        ];
    }
}