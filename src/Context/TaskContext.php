<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

class TaskContext extends Context implements ContextInterface
{
    /**
     * task lock timeout in millisecond. For more details, see Locking.
     * Default: 300000
     *
     * @param int $taskLockTimeout
     *
     * @return $this;
     */
    public function setTaskLockTimeout(int $taskLockTimeout): self
    {
        $this->properties['taskLockTimeout'] = $taskLockTimeout;

        return $this;
    }

    /**
     * Different based on task types.
     * Defaults:
     * Realtime index task    75
     * Batch index task    50
     * Merge/Append/Compaction task    25
     * Other tasks    0
     *
     * @param int $priority
     *
     * @return $this;
     */
    public function setPriority(int $priority): self
    {
        $this->properties['priority'] = $priority;

        return $this;
    }
}