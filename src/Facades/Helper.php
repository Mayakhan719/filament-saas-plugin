<?php

namespace Maya\FilamentSaasPlugin\Facades;

use Illuminate\Support\Facades\Facade;

class Helper extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'helper';
    }
}
