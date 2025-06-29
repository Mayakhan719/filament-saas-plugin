<?php

namespace Maya\FilamentSaasPlugin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravelcm\Subscriptions\Traits\HasPlanSubscriptions;
use Illuminate\Support\Str;

class Team extends Model
{
    use HasFactory, HasPlanSubscriptions;

    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug(strtolower($model->name));
        });
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Active team';
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('filament-saas-plugin.user_model', App\Models\User::class));
    }
}
