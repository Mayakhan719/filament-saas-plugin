<?php

namespace Maya\FilamentSaasPlugin\Traits;

use Maya\FilamentSaasPlugin\Models\Team;
use Filament\Facades\Filament;
use Filament\Navigation\UserMenuItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Filament\Panel;

trait HasSaasFeatures
{

    // ────────────────────────────
    // Relationships
    // ────────────────────────────

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    public function latestTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'latest_team_id');
    }

    // ────────────────────────────
    // Filament Multitenancy
    // ────────────────────────────

    public function getTenants(Panel $panel): Collection
    {
        return $this->teams;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->teams()->whereKey($tenant->getKey())->exists();
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        if ($this->latest_team_id && $this->latestTeam && $this->canAccessTenant($this->latestTeam)) {
            return $this->latestTeam;
        }

        return $this->teams()->latest('pivot_created_at')->first() ?? $this->teams()->first();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Adjust if needed (e.g., based on roles or flags)
    }

    // ────────────────────────────
    // Helpers
    // ────────────────────────────

    public function setLatestTeam(Team $team): void
    {
        if ($this->canAccessTenant($team)) {
            $this->update(['latest_team_id' => $team->id]);
        }
    }
}
