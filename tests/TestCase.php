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
     * @param mixed  $value
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

    /**
     * Tear down the test case.
     *
     * @return void
     */
    public function tearsDown(): void
    {

        parent::tearDown();
        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
        Mockery::close();
    }
}