<?php

namespace tests\Level23\Druid\Context;

use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Context\GroupByQueryContext;
use Level23\Druid\Context\TimeSeriesQueryContext;
use Level23\Druid\Context\TopNQueryContext;
use tests\TestCase;

class ContextTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [GroupByQueryContext::class, 'v1'],
            [GroupByQueryContext::class, 'v2'],
            [TopNQueryContext::class],
            [TimeSeriesQueryContext::class],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $class
     * @param string $version
     */
    public function testContext(string $class, string $version = '')
    {
        $object     = new $class([]);
        $properties = get_object_vars($object);

        foreach ($properties as $property => $value) {
            if ($property == 'groupByStrategy' && $object instanceof GroupByQueryContext) {
                $word = $version;
            } else {
                $word = $this->getRandomWord();
            }

            $object->$property     = $word;
            $properties[$property] = $word;
        }

        if ($object instanceof GroupByQueryContext) {

            if ($object->groupByStrategy == 'v2') {
                $unset = [
                    'maxIntermediateRows',
                    'maxResults',
                    'useOffheap',
                ];
            } else {
                $unset = [
                    'bufferGrouperInitialBuckets',
                    'bufferGrouperMaxLoadFactor',
                    'forceHashAggregation',
                    'intermediateCombineDegree',
                    'numParallelCombineThreads',
                    'sortByDimsFirst',
                    'forceLimitPushDown',
                    'maxMergingDictionarySize',
                    'maxOnDiskStorage',
                ];
            }

            foreach ($unset as $p) {
                unset($properties[$p]);
            }
        }

        if ($object instanceof ContextInterface) {
            $this->assertEquals($properties, $object->getContext());
        }
    }

    public function testSettingValueUsingConstructor()
    {
        $context = new GroupByQueryContext(['timeout' => 6271]);

        $this->assertEquals(6271, $context->timeout);
    }

    public function testNonExistingProperty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('was not found in the');

        new GroupByQueryContext(['prio' => 1]);
    }

    public function testNonScalarValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value');
        new GroupByQueryContext(['priority' => ['oops']]);
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