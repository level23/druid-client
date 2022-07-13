<?php

declare(strict_types=1);

namespace Level23\Druid\Collections;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use InvalidArgumentException;

/**
 * @template T
 * @implements ArrayAccess<int, T>
 * @implements IteratorAggregate<int, T>
 */
abstract class BaseCollection implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @var array<int, T>
     */
    protected array $items;

    /**
     * @return \ArrayIterator<int, T>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Add an item to our collection.
     *
     * @param T ...$item
     */
    public function add(...$item): void
    {
        $type = $this->getType();

        foreach ($item as $obj) {
            if (!$obj instanceof $type) {
                throw new InvalidArgumentException('We only accept instances of type ' . $type);
            }

            $this->items[] = $obj;
        }
    }

    /**
     * Return an array representation of our items
     *
     * @return array<int, int|string|array<mixed>>
     */
    abstract public function toArray(): array;

    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    abstract public function getType(): string;

    /**
     * Whether an offset exists
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param int $offset   <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be cast to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param int $offset   <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return null|T
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * Offset to set
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param int|null $offset <p>
     *                         The offset to assign the value to.
     *                         </p>
     * @param T        $value  <p>
     *                         The value to set.
     *                         </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $type = $this->getType();
        if (!$value instanceof $type) {
            throw new InvalidArgumentException('We only accept ' . $type . ' as values!');
        }

        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param int $offset   <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * Count elements of an object
     *
     * @link  https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return count($this->items);
    }
}