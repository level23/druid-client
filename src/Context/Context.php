<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

use InvalidArgumentException;

abstract class Context
{
    /**
     * @var array<string,string|int|bool>
     */
    protected array $properties = [];

    /**
     * Context constructor.
     *
     * @param array<string,string|int|bool> $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $key => $value) {

            $method = 'set' . $key;

            if (!is_scalar($value)) {
                throw new InvalidArgumentException(
                    'Invalid value ' . var_export($value, true) .
                    ' for ' . $key . ' in ' . __CLASS__
                );
            }

            $callable = [$this, $method];
            if (!is_callable($callable)) {
                // From now on, we support setting properties where no setters are defined for.
                // This is because the context settings can vary per version. In this way we can support new
                // non-existing properties.
                $this->properties[$key] = $value;
                continue;
            }

            call_user_func($callable, $value);
        }
    }

    /**
     * Return the context as it can be used in the druid query.
     *
     * @return array<string,string|int|bool>
     */
    public function toArray(): array
    {
        return $this->properties;
    }
}