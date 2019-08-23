<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

class StringFormatExtraction implements ExtractionInterface
{
    /**
     * @var string
     */
    protected $sprintfExpression;

    public function __construct(string $sprintfExpression)
    {
        $this->sprintfExpression = $sprintfExpression;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name'   => 'stringFormat',
            'format' => $this->sprintfExpression,
        ];
    }
}