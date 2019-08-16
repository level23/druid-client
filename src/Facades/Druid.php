<?php

namespace Level23\Druid\Facades;

use Illuminate\Support\Facades\Facade;

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