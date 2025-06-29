<?php

namespace Maya\FilamentSaasPlugin\Http\Middleware;

use Maya\FilamentSaasPlugin\Models\User;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DefaultTeamVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Filament::getTenant()) {
            $user = Auth::user();
            $currentTenant = Filament::getTenant();
            if ($user instanceof User && $currentTenant) {
                if ($user->latest_team_id !== $currentTenant->id) {
                    $user->setLatestTeam($currentTenant);
                }
            }
            setPermissionsTeamId($currentTenant->id);
        }
        return $next($request);
    }
}
