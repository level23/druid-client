<?php
declare(strict_types=1);

namespace Level23\Druid\Tests;

use Mockery;
use ReflectionClass;

class TestCase extends \PHPUnit\Framework\TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @param object $object
     * @param string $propertyName
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getProperty(object $object, string $propertyName)
    {
        $reflectionClass = new ReflectionClass($object);

        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    public function assertArrayContainsSubset(array $expectedSubset, array $actualArray): void
    {
        foreach ($expectedSubset as $key => $value) {
            $this->assertArrayHasKey($key, $actualArray);
            $this->assertSame($value, $actualArray[$key]);
        }
    }
}