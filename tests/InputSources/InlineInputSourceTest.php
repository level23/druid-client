<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputSources\InlineInputSource;

class InlineInputSourceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testInlineInputSourceJson(): void
    {
        $inline = new InlineInputSource(
            [
                [true, 18, "male"],
                [false, 92, "female"],
            ]
        );

        $this->assertEquals([
            'type' => 'inline',
            'data' => '[true,18,"male"]' . "\n" . '[false,92,"female"]',
        ], $inline->toArray());

        $inline = new InlineInputSource(
            [
                [true, 18, "male"],
                [false, 92, "female"],
            ],
            'json'
        );

        $this->assertEquals([
            'type' => 'inline',
            'data' => '[true,18,"male"]' . "\n" . '[false,92,"female"]',
        ], $inline->toArray());
    }

    /**
     * @throws \Exception
     */
    public function testInlineInputSourceCsv(): void
    {
        $inline = new InlineInputSource(
            [
                [true, 18, "I'm a male"],
                [false, 92, "Me is female"],
            ],
            'csv'
        );

        $this->assertEquals([
            'type' => 'inline',
            'data' => '1,18,"I\'m a male"' . "\n" . ',92,"Me is female"' . "\n",
        ], $inline->toArray());
    }

    public function testInvalidInputFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The given input format is invalid: wrong. Allowed are: csv,json');
        new InlineInputSource(['hi'], "wrong");
    }
}