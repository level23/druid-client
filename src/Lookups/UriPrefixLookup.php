<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups;

use Level23\Druid\Lookups\ParseSpecs\ParseSpecInterface;

/**
 * @see https://druid.apache.org/docs/latest/querying/lookups-cached-global#uri-lookup
 * @internal
 */
class UriPrefixLookup implements LookupInterface
{
    /**
     * @param ParseSpecInterface $parseSpec
     * @param string             $uriPrefix
     * @param string|null        $fileRegex
     * @param string|null        $pollPeriod          The pollPeriod value specifies the period in ISO 8601 format
     *                                                between checks for replacement data for the lookup. For example
     *                                                PT15M. When not given, it is only once. If the source of the
     *                                                lookup is capable of providing a timestamp, the lookup will only
     *                                                be updated if it has changed since the prior tick of pollPeriod.
     *                                                A value of 0, an absent parameter, or null all mean populate once
     *                                                and do not attempt to look for new data later. Whenever an poll
     *                                                occurs, the updating system will look for a file with the most
     *                                                recent timestamp and assume that one with the most recent data
     *                                                set, replacing the local cache of the lookup data.
     * @param int|null           $maxHeapPercentage
     * @param bool               $injective           If the underlying map is injective (keys and values are unique)
     *                                                then optimizations can occur internally by setting this to true
     * @param int                $firstCacheTimeoutMs How long to wait (in ms) for the first run of the cache to
     *                                                populate. 0 indicates to not  wait
     *
     */
    public function __construct(
        protected ParseSpecInterface $parseSpec,
        protected string $uriPrefix,
        protected ?string $fileRegex = null,
        protected ?string $pollPeriod = null,
        protected ?int $maxHeapPercentage = null,
        protected bool $injective = false,
        protected int $firstCacheTimeoutMs = 0
    ) {

    }

    public function toArray(): array
    {
        $response = [
            'type'               => 'uri',
            'uriPrefix'          => $this->uriPrefix,
            'namespaceParseSpec' => $this->parseSpec->toArray(),
        ];

        if ($this->fileRegex !== null) {
            $response['fileRegex'] = $this->fileRegex;
        }

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