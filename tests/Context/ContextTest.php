<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Context;

use Exception;
use ReflectionMethod;
use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Context\TopNQueryContext;
use Level23\Druid\Context\ScanQueryContext;
use Level23\Druid\Context\GroupByV2QueryContext;
use Level23\Druid\Context\GroupByV1QueryContext;
use Level23\Druid\Context\TimeSeriesQueryContext;

class ContextTest extends TestCase
{
    /**
     * @return array<array<array<string,string>|string>>
     */
    public static function dataProvider(): array
    {
        return [
            [GroupByV1QueryContext::class, ['groupByStrategy' => 'v1']],
            [GroupByV2QueryContext::class, ['groupByStrategy' => 'v2']],
            [TopNQueryContext::class],
            [TimeSeriesQueryContext::class],
            [TaskContext::class],
            [ScanQueryContext::class],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string               $class
     * @param array<string,string> $extra
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testContext(string $class, array $extra = []): void
    {
        $methods = get_class_methods($class);

        $object = new $class([]);

        $properties = [];

        foreach ($methods as $method) {
            if (!str_starts_with($method, 'set')) {
                continue;
            }

            $property = lcfirst(substr($method, 3));

            $reflection = new ReflectionMethod($class, $method);
            $parameters = $reflection->getParameters();

            switch ($parameters[0]->getType()->getName()) {
                case 'int':
                    $value = rand(1, 1000);
                    break;

                case 'string':
                    $value = $this->getRandomWord();
                    break;

                case 'bool':
                    $items = [true, false];
                    $value = $items[array_rand($items)];
                    break;

                default:
                    throw new Exception('Unknown type: ' . $parameters[0]->getType());
            }

            $properties[$property] = $value;

            // call our setter.
            $response = $object->$method($value);

            $this->assertEquals($object, $response);
        }

        $expectedProperties = array_merge($properties, $extra);

        if ($object instanceof ContextInterface) {
            $this->assertEquals($expectedProperties, $object->toArray());
        }

        $object2 = new $class($properties);
        if ($object2 instanceof ContextInterface) {
            $this->assertEquals($expectedProperties, $object2->toArray());
        }
    }

    public function testNonExistingProperty(): void
    {
        $context = new GroupByV2QueryContext(['something' => 1]);

        $properties = $context->toArray();

        $this->assertArrayHasKey('something', $properties);
        $this->assertEquals(1, $properties['something']);
    }

    public function testNonScalarValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value');

        /** @noinspection PhpParamsInspection */
        // @phpstan-ignore-next-line
        new GroupByV2QueryContext(['priority' => ['oops']]);
    }

    protected function getRandomWord(): string
    {
        $characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < 10; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}
