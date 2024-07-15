<?php
declare(strict_types=1);

namespace Level23\Druid\Tests;

use Mockery;
use ReflectionClass;
use Mockery\MockInterface;
use Mockery\LegacyMockInterface;

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
    public static function getProperty(object $object, string $propertyName): mixed
    {
        $reflectionClass = new ReflectionClass($object);

        $property = $reflectionClass->getProperty($propertyName);
        /** @noinspection PhpExpressionResultUnusedInspection */
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @param array<int|string,mixed> $expectedSubset
     * @param array<int|string,mixed> $actualArray
     *
     * @return void
     */
    public function assertArrayContainsSubset(array $expectedSubset, array $actualArray): void
    {
        foreach ($expectedSubset as $key => $value) {
            $this->assertArrayHasKey($key, $actualArray);
            $this->assertSame($value, $actualArray[$key]);
        }
    }

    /**
     * Get a mockery mock which we can use to check our constructor call.
     * Make sure to add this in your PHPDoc annotations:
     *
     * * @ runInSeparateProcess
     * * @ preserveGlobalState disabled
     *
     * @param string      $class
     * @param class-string|null $interface
     *
     * @return LegacyMockInterface|MockInterface
     */
    protected function getConstructorMock(string $class, ?string $interface = null): LegacyMockInterface|MockInterface
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        if ($interface !== null) {
            $builder->addTarget($interface);
        }

        return Mockery::mock($builder);
    }
}