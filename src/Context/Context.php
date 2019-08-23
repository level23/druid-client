<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

use InvalidArgumentException;

abstract class Context
{
    /**
     * @var array
     */
    protected $properties = [];

    /**
     * Context constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $key => $value) {

            $method = 'set' . ucfirst($key);

            $callable = [$this, $method];
            if (!method_exists($this, $method) || !is_callable($callable)) {
                throw new InvalidArgumentException(
                    'Setting ' . $key . ' was not found in ' . __CLASS__
                );
            }

            if (!is_scalar($value)) {
                throw new InvalidArgumentException(
                    'Invalid value ' . var_export($value, true) .
                    ' for ' . $key . ' in ' . __CLASS__
                );
            }

            call_user_func($callable, $value);
        }
    }

    /**
     * Return the context as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_filter($this->properties, function ($value) {
            return ($value !== null);
        });
    }
}