<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

use Level23\Druid\Types\NullHandling;

class StringFormatExtraction implements ExtractionInterface
{
    /**
     * @var string
     */
    protected string $sprintfExpression;

    /**
     * @var string
     */
    protected string $nullHandling;

    public function __construct(string $sprintfExpression, string $nullHandling = NullHandling::NULL_STRING)
    {
        $this->sprintfExpression = $sprintfExpression;
        $this->nullHandling      = NullHandling::validate($nullHandling);
    }

    /**
     * Return the Extraction Function, so it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name'         => 'stringFormat',
            'format'       => $this->sprintfExpression,
            'nullHandling' => $this->nullHandling,
        ];
    }
}