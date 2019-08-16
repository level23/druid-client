<?php

declare(strict_types=1);

namespace Level23\Druid\Collections;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use InvalidArgumentException;

abstract class BaseCollection implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Add an item to our collection.
     *
     * @param mixed $item
     */
    public function add($item)
    {
        $type = $this->getType();
        if (!$item instanceof $type) {
            throw new InvalidArgumentException('We only accept instances of type ' . $type);
        }

        $this->items[] = $item;
    }

    /**
     * Return an array representation of our items
     *
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    abstract public function getType(): string;

    /**
     * Whether a offset exists
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * Offset to set
     *
     * @link  https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
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
     * @param mixed $offset <p>
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
    public function count()
    {
        return count($this->items);
    }
}