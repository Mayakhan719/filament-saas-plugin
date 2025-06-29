<?php

namespace Maya\FilamentSaasPlugin\Contracts;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;

interface SaasUser extends FilamentUser, HasTenants, HasDefaultTenant
{
    
}
