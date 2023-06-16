<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\TuningConfig;

use Exception;
use ReflectionMethod;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\TuningConfig\TuningConfig;

class TuningConfigTest extends TestCase
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testContext(): void
    {
        $methods = get_class_methods(TuningConfig::class);

        $tuningConfig = new TuningConfig([]);

        $properties = [];

        foreach ($methods as $method) {
            if (!str_starts_with($method, 'set')) {
                continue;
            }

            $property = lcfirst(substr($method, 3));

            $reflection = new ReflectionMethod(TuningConfig::class, $method);
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

                case 'array':
                    $value = ['item' => $this->getRandomWord()];
                    break;

                default:
                    throw new Exception('Unknown type: ' . $parameters[0]->getType());
            }

            $properties[$property] = $value;

            // call our setter.
            $response = $tuningConfig->$method($value);

            $this->assertEquals($response, $tuningConfig);
        }

        $this->assertEquals($properties, $tuningConfig->toArray());
    }

    public function testSettingValueUsingConstructor(): void
    {
        $context = new TuningConfig(['maxrowspersegment' => '1']);

        $response = $context->toArray();
        $this->assertEquals('1', $response['maxRowsPerSegment']);
    }

    public function testNonExistingProperty(): void
    {
        $tuningConfig = new TuningConfig(['something' => 1]);
        $data         = $tuningConfig->toArray();

        $this->assertArrayHasKey('something', $data);
        $this->assertEquals(1, $data['something']);
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
