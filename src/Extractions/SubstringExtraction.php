<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

/**
 * Class SubstringExtraction
 *
 * Substring Extraction Function
 *
 * Returns a substring of the dimension value starting from the supplied index and of the
 * desired length. Both index and length are measured in the number of Unicode code units
 * present in the string as if it were encoded in UTF-16. Note that some Unicode characters
 * may be represented by two code units. This is the same behavior as the Java String class's "substring" method.
 *
 * If the desired length exceeds the length of the dimension value, the remainder of the
 * string starting at index will be returned. If index is greater than the length of the dimension
 * value, null will be returned.
 *
 * { "type" : "substring", "index" : 1, "length" : 4 }
 *
 * The length may be omitted for substring to return the remainder of the dimension value starting
 * from index, or null if index greater than the length of the dimension value.
 *
 * { "type" : "substring", "index" : 3 }
 *
 * @package Level23\Druid\Extractions
 */
class SubstringExtraction implements ExtractionInterface
{
    /**
     * @var int
     */
    protected int $index;

    /**
     * @var int|null
     */
    protected ?int $length;

    /**
     * RegexExtraction constructor.
     *
     * @param int      $index
     * @param int|null $length
     */
    public function __construct(int $index, ?int $length = null)
    {
        $this->index  = $index;
        $this->length = $length;
    }

    /**
     * Return the Extraction Function, so it can be used in a druid query.
     *
     * @return array<string,string|int>
     */
    public function toArray(): array
    {
        $response = [
            'type'  => 'substring',
            'index' => $this->index,
        ];

        if ($this->length !== null) {
            $response['length'] = $this->length;
        }

        return $response;
    }
}