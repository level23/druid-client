<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class UpperExtraction implements ExtractionInterface
{
    /**
     * @var string|null
     */
    protected $locale;

    public function __construct(string $locale = null)
    {
        $this->locale = $locale;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type' => 'upper',
        ];

        if ($this->locale) {
            $result['locale'] = $this->locale;
        }

        return $result;
    }
}