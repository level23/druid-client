<?php
declare(strict_types=1);

/** @noinspection PhpUndefinedNamespaceInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace Level23\Druid\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Druid
 *
 * @package Level23\Druid\Facades
 * @codeCoverageIgnore
 */
class Druid extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'druid';
    }
}