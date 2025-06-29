<?php

namespace Maya\FilamentSaasPlugin\Http\Middleware;

use Maya\FilamentSaasPlugin\Filament\Client\Pages\Billing;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBillableIsSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();
        if ($tenant && $tenant->activePlanSubscriptions()->isEmpty()) {
            return redirect(Billing::getUrl());
        }
        return $next($request);
    }
}
