<?php

namespace tests;

use Mockery;
use ReflectionClass;

class TestCase extends \PHPUnit\Framework\TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @param mixed  $object
     * @param string $propertyName
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getProperty($object, string $propertyName)
    {
        $reflectionClass = new ReflectionClass($object);

        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}