<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Tasks;

use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Tasks\KillTaskBuilder;

class KillTaskBuilderTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testKillTaskBuilder(): void
    {
        $client  = new DruidClient([]);
        $builder = new KillTaskBuilder($client, "wikipedia");

        $this->assertEquals(false, $this->getProperty($builder, "markAsUnused"));
        $this->assertEquals("wikipedia", $this->getProperty($builder, "dataSource"));

        $this->assertEquals($builder, $builder->markAsUnused(true));
        $this->assertEquals(true, $this->getProperty($builder, "markAsUnused"));

        $this->assertEquals($builder, $builder->markAsUnused(false));
        $this->assertEquals(false, $this->getProperty($builder, "markAsUnused"));
    }

    /**
     * @testWith [null, null]
     *           [null, true]
     *           [null, false]
     *           ["task-1337", null]
     *           ["task-1337", true]
     *           ["task-1337", false]
     *
     * @param string|null $taskId
     * @param bool|null   $markAsUnused
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testBuildTask(?string $taskId, ?bool $markAsUnused): void
    {
        $client  = new DruidClient([]);
        $builder = new KillTaskBuilder($client, "wikipedia");

        if ($markAsUnused !== null) {
            $builder->markAsUnused($markAsUnused);
        } else {
            $builder->markAsUnused();
        }

        $expected = [
            'type'         => 'kill',
            'dataSource'   => 'wikipedia',
            'interval'     => '2019-01-10T00:00:00.000Z/2019-01-12T00:00:00.000Z',
            'markAsUnused' => $markAsUnused ?? true,
        ];
        $builder->interval('10-01-2019/12-01-2019');
        if ($taskId) {
            $builder->taskId($taskId);
            $expected['id'] = $taskId;
        }

        $this->assertEquals($expected, $builder->toArray());
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testBuildTaskWithoutInterval(): void
    {
        $client  = new DruidClient([]);
        $builder = new KillTaskBuilder($client, "wikipedia");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify an interval!');

        $builder->toArray();
    }
}
