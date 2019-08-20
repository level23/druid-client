<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

use InvalidArgumentException;

class TaskContext implements ContextInterface
{
    /**
     * task lock timeout in millisecond. For more details, see Locking.
     * Default: 300000
     *
     * @var int
     */
    public $taskLockTimeout;

    /**
     * Different based on task types.
     * Defaults:
     * Realtime index task    75
     * Batch index task    50
     * Merge/Append/Compaction task    25
     * Other tasks    0
     *
     * @var int
     */
    public $priority;

    /**
     * TaskContext constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        foreach ($properties as $key => $value) {

            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException(
                    'Setting ' . $key . ' was not found in the ' . __CLASS__ . ' context'
                );
            }

            if (!is_scalar($value)) {
                throw new InvalidArgumentException(
                    'Invalid value ' . var_export($value, true) .
                    ' for ' . $key . ' for the task context'
                );
            }

            $this->$key = $value;
        }
    }

    /**
     * Return the context as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        $properties = [
            'taskLockTimeout',
            'priority',
        ];

        foreach ($properties as $property) {
            if ($this->$property !== null) {
                $result[$property] = $this->$property;
            }
        }

        return $result;
    }
}