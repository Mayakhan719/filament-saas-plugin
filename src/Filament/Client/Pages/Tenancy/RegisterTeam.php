<?php

namespace Maya\FilamentSaasPlugin\Filament\Client\Pages\Tenancy;

use Maya\FilamentSaasPlugin\Http\Middleware\VerifyBillableIsSubscribed;
use Maya\FilamentSaasPlugin\Models\Team;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maya\FilamentSaasPlugin\Models\Permission;
use Maya\FilamentSaasPlugin\Models\Role;

class RegisterTeam extends RegisterTenant
{
    protected static string | array $withoutRouteMiddleware = VerifyBillableIsSubscribed::class;

    public static function getLabel(): string
    {
        return 'Register Company';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Company Name')
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug(strtolower($state)));
                    }),

                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->hidden(),
            ]);
    }

    protected function handleRegistration(array $data): Team
    {
        $team = Team::create($data);
        $team->users()->attach(Auth::id());
        return $team;
    }
}
