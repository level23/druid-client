<?php

namespace tests\Level23\Druid\Context;

use InvalidArgumentException;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Context\GroupByV1QueryContext;
use Level23\Druid\Context\GroupByV2QueryContext;
use Level23\Druid\Context\TimeSeriesQueryContext;
use Level23\Druid\Context\TopNQueryContext;
use tests\TestCase;

class ContextTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [GroupByV1QueryContext::class, ['groupByStrategy' => 'v1']],
            [GroupByV2QueryContext::class, ['groupByStrategy' => 'v2']],
            [TopNQueryContext::class],
            [TimeSeriesQueryContext::class],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $class
     * @param string $version
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testContext(string $class, array $extra = [])
    {
        $methods = get_class_methods($class);

        $object = new $class([]);

        $properties = [];

        foreach ($methods as $method) {
            if (substr($method, 0, 3) != 'set') {
                continue;
            }

            $property = lcfirst(substr($method, 3));

            $reflection = new \ReflectionMethod($class, $method);
            $parameters = $reflection->getParameters();

            switch ($parameters[0]->getType()) {
                case 'int':
                    $value = rand(1, 1000);
                    break;

                case 'string':
                    $value = $this->getRandomWord();
                    break;

                case 'bool':
                    $value = array_rand([true, false]);
                    break;

                default:
                    throw new \Exception('Unknown type: ' . $parameters[0]->getType());
            }

            $properties[$property] = $value;

            // call our setter.
            $object->$method($value);
        }

        $properties = array_merge($properties, $extra);

        if ($object instanceof ContextInterface) {
            $this->assertEquals($properties, $object->toArray());
        }
    }

    public function testSettingValueUsingConstructor()
    {
        $context = new GroupByV2QueryContext(['timeout' => 6271]);

        $response = $context->toArray();
        $this->assertEquals(6271, $response['timeout']);
    }

    public function testNonExistingProperty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('was not found in ');

        new GroupByV2QueryContext(['prio' => 1]);
    }

    public function testNonScalarValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value');
        new GroupByV2QueryContext(['priority' => ['oops']]);
    }

    protected function getRandomWord()
    {
        $characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < 10; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}