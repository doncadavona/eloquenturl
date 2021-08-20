<?php

namespace Doncadavona\Eloquenturl\Facades;

use Illuminate\Support\Facades\Facade;

class Eloquenturl extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'eloquenturl';
    }
}
