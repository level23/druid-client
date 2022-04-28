<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use ReflectionClass;
use ReflectionException;

abstract class Enum
{
    /**
     * @var array<string,array<string,scalar>>
     */
    protected static array $constCacheArray = [];

    /**
     * @return array<string,scalar>
     * @codeCoverageIgnore
     */
    protected static function getConstants(): array
    {
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            try {
                $reflect = new ReflectionClass($calledClass);

                /** @var array<string,scalar> $values */
                $values = $reflect->getConstants();
                
                self::$constCacheArray[$calledClass] = $values;
            } catch (ReflectionException $e) {
                return [];
            }
        }

        return self::$constCacheArray[$calledClass];
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isValidValue(string $value): bool
    {
        $values = array_values(self::getConstants());

        return in_array($value, $values, true);
    }

    /**
     * Return all values
     *
     * @return array<scalar>
     */
    public static function values(): array
    {
        return array_values(self::getConstants());
    }
}
