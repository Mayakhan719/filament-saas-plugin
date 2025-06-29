<?php

namespace Maya\FilamentSaasPlugin\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;

class ExtendedAuthenticate extends FilamentAuthenticate
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        return Filament::getPanel('client')->getLoginUrl();
    }
}
