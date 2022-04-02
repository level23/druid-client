<?php
declare(strict_types=1);

namespace Level23\Druid\Types;

use ReflectionClass;
use ReflectionException;

abstract class Enum
{
    /**
     * @var null|array
     */
    protected static ?array $constCacheArray = null;

    /**
     * @return array
     * @codeCoverageIgnore
     */
    protected static function getConstants(): array
    {
        if (self::$constCacheArray == null) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            try {
                $reflect = new ReflectionClass($calledClass);

                self::$constCacheArray[$calledClass] = $reflect->getConstants();
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
     * @return array
     */
    public static function values(): array
    {
        return array_values(self::getConstants());
    }
}
