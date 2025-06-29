<?php

namespace Maya\FilamentSaasPlugin\Filament\Resources\PlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Laravelcm\Subscriptions\Interval;

class FeatureManager extends RelationManager
{
    protected static string $relationship = 'features';

    public static function getLabel(): ?string
    {
        return 'Features';
    }

    public static function getModelLabel(): ?string
    {
        return 'Feature';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Features';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                TextInput::make('value')
                    ->label('Value')
                    ->columnSpanFull()
                    ->default(0)
                    ->required(),
                Forms\Components\Select::make('resettable_interval')
                    ->label('Resettable Interval')
                    ->default(Interval::DAY->value)
                    ->options([
                        Interval::DAY->value => 'Day',
                        Interval::MONTH->value => 'Month',
                        Interval::YEAR->value => 'Year',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('resettable_period')
                    ->label('Resettable Period')
                    ->required()
                    ->default(0)
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->recordTitleAttribute('feature')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('resettable_interval')
                    ->label('Resettable Interval')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('resettable_period')
                    ->label('Resettable Period')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
