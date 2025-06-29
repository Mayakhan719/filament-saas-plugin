<?php

namespace Maya\FilamentSaasPlugin\Filament\Client\Pages\Tenancy;

use Maya\FilamentSaasPlugin\Http\Middleware\VerifyBillableIsSubscribed;
use Maya\FilamentSaasPlugin\Models\City;
use Maya\FilamentSaasPlugin\Models\Country;
use Maya\FilamentSaasPlugin\Models\State;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EditTeamProfile extends EditTenantProfile
{
    protected static string | array $withoutRouteMiddleware = VerifyBillableIsSubscribed::class;

    public static function getLabel(): string
    {
        return 'Company profile';
    }

    public function form(Form $form): Form
    {

        return $form

            ->schema([
                Section::make('Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Company Name')
                            ->required(),
                    ])

            ]);
    }
}
