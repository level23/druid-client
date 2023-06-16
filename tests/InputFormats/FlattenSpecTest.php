<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use ValueError;
use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\FlattenFieldType;
use Level23\Druid\InputFormats\FlattenSpec;

class FlattenSpecTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *           [null]
     *
     * @param bool|null $useFieldDiscovery
     *
     * @return void
     */
    public function testFlattenSpec(?bool $useFieldDiscovery): void
    {
        if ($useFieldDiscovery === null) {
            $flattenSpec = new FlattenSpec();
        } else {
            $flattenSpec = new FlattenSpec($useFieldDiscovery);
        }

        $flattenSpec->field(FlattenFieldType::PATH, 'myField1', 'input.a.b');
        $flattenSpec->field(FlattenFieldType::ROOT, 'myField2');

        $this->assertEquals(
            [
                'useFieldDiscovery' => $useFieldDiscovery ?? true,
                'fields'            => [
                    [
                        'type' => 'path',
                        'name' => 'myField1',
                        'expr' => 'input.a.b',
                    ],
                    [
                        'type' => 'root',
                        'name' => 'myField2',
                    ],
                ],
            ],
            $flattenSpec->toArray());
    }

    public function testWithWrongType(): void
    {
        $spec = new FlattenSpec();

        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('"wrong" is not a valid backing value for enum Level23\Druid\Types\FlattenFieldType');

        $spec->field('wrong', 'field');
    }

    /**
     * @testWith ["jq"]
     *           ["path"]
     *
     * @param string $type
     *
     * @return void
     */
    public function testWithEmptyExpr(string $type): void
    {
        $spec = new FlattenSpec();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('For type JQ or PATH, you need to specify the expression!');

        $spec->field($type, 'field');
    }
}