<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups;

use Level23\Druid\Lookups\ParseSpecs\ParseSpecInterface;

/**
 * @see https://druid.apache.org/docs/latest/querying/lookups-cached-global#uri-lookup
 */
class UriLookup implements LookupInterface
{
    public function __construct(
        protected ParseSpecInterface $parseSpec,
        protected string $uri,
        protected null|int|string $pollPeriod = null,
        protected ?int $maxHeapPercentage = null,
        protected bool $injective = false,
        protected int $firstCacheTimeoutMs = 0
    ) {

    }

    public function toArray(): array
    {
        $response = [
            'type'               => 'uri',
            'uri'                => $this->uri,
            'namespaceParseSpec' => $this->parseSpec->toArray(),
        ];

        if ($this->pollPeriod !== null) {
            $response['pollPeriod'] = $this->pollPeriod;
        }

        if ($this->maxHeapPercentage !== null) {
            $response['maxHeapPercentage'] = $this->maxHeapPercentage;
        }

        return [
            'type'                => 'cachedNamespace',
            'extractionNamespace' => $response,
            'injective'           => $this->injective,
            'firstCacheTimeout'   => $this->firstCacheTimeoutMs,
        ];
    }
}