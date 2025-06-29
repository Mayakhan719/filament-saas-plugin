<?php

namespace Maya\FilamentSaasPlugin;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Maya\FilamentSaasPlugin\Skeleton\SkeletonClass
 */
class FilamentSaasPluginFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'filament-saas-plugin';
    }
}
