<?php

namespace Maya\FilamentSaasPlugin\Filament\Resources;

use Maya\FilamentSaasPlugin\Facades\FilamentSubscriptions;
use Maya\FilamentSaasPlugin\Filament\Resources\SubscriptionResource\Pages;
use Maya\FilamentSaasPlugin\Filament\Resources\SubscriptionResource\RelationManagers;
use Maya\FilamentSaasPlugin\Models\Team;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Subscription;

class SubscriptionResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Subscriptions';
    protected static ?int $navigationSort = 2;

    public static function getModel(): string
    {
        return config('laravel-subscriptions.models.subscription', Subscription::class);
    }

    public static function getNavigationLabel(): string
    {
        return 'Subscriptions';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Subscriptions';
    }

    public static function getLabel(): ?string
    {
        return 'Subscriptions';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('name'),

                Forms\Components\Select::make('subscriber_type')
                    ->label('subscriber_type')
                    ->options(count(FilamentSubscriptions::getOptions()) ? FilamentSubscriptions::getOptions()->pluck('name', 'model')->toArray() : [Team::class => 'Team'])
                    ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => $set('subscriber_id', null))
                    ->preload()
                    ->live()
                    ->searchable(),

                Forms\Components\Select::make('subscriber_id')
                    ->label('subscriber')
                    ->options(fn(Forms\Get $get) => $get('subscriber_type') ? $get('subscriber_type')::pluck('name', 'id')->toArray() : [])
                    ->searchable(),

                Forms\Components\Select::make('plan_id')
                    ->columnSpanFull()
                    ->searchable()
                    ->label('Plan')
                    ->options(Plan::query()->where('is_active', 1)->pluck('name', 'id')->toArray())
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                        $set('name', $get('plan_id') ? Plan::find($get('plan_id'))->name : null);
                    })
                    ->required(),
                Forms\Components\Toggle::make('use_custom_dates')
                    ->columnSpanFull()
                    ->label('Use Custom Dates')
                    ->live()
                    ->required(),

                Forms\Components\DatePicker::make('trial_ends_at')
                    ->visible(fn(Forms\Get $get) => $get('use_custom_dates'))
                    ->label('Trial Ends At')
                    ->required(fn(Forms\Get $get) => $get('use_custom_dates')),
                Forms\Components\DatePicker::make('starts_at')
                    ->visible(fn(Forms\Get $get) => $get('use_custom_dates'))
                    ->label('Starts At')
                    ->required(fn(Forms\Get $get) => $get('use_custom_dates')),
                Forms\Components\DatePicker::make('ends_at')
                    ->visible(fn(Forms\Get $get) => $get('use_custom_dates'))
                    ->label('Ends At')
                    ->required(fn(Forms\Get $get) => $get('use_custom_dates')),
                Forms\Components\DatePicker::make('canceled_at')
                    ->visible(fn(Forms\Get $get) => $get('use_custom_dates'))
                    ->label('Canceled At')
                    ->required(fn(Forms\Get $get) => $get('use_custom_dates')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subscriber.name')
                    ->label('Subscriber')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->state(fn($record) => $record->active())
                    ->boolean()
                    ->label('Active')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('trial_ends_at')->dateTime()
                    ->label('Trial Ends At')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()
                    ->label('Starts At')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()
                    ->label('Ends At')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('canceled_at')->dateTime()
                    ->label('Canceled At')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('Date Range')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->required(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['start_date']) || !isset($data['end_date'])) {
                            return $query;
                        }
                        return $query->whereBetween('starts_at', [$data['start_date'], $data['end_date']]);
                    }),
                Tables\Filters\Filter::make('Canceled')
                    ->form([
                        Forms\Components\Select::make('canceled')
                            ->options([
                                '' => 'All',
                                '1' => 'Yes',
                                '0' => 'No',
                            ])
                            ->label('Canceled')
                            ->required(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['canceled'])) {
                            return $query;
                        }
                        if ($data['canceled'] === '1') {
                            return $query->whereNotNull('canceled_at');
                        }
                        if ($data['canceled'] === '0') {
                            return $query->whereNull('canceled_at');
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Edit')
                    ->iconButton(),
                Tables\Actions\Action::make('cancel')
                    ->visible(fn($record) => $record->active())
                    ->iconButton()
                    ->label('Cancel')
                    ->tooltip('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->action(function (Subscription $record) {
                        $record->cancel(true);

                        Notification::make()
                            ->title('Canceled')
                            ->body('Subscription canceled successfully.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('renew')
                    ->visible(fn($record) => $record->ended())
                    ->iconButton()
                    ->label('Renew')
                    ->tooltip('Renew')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('info')
                    ->action(function (Subscription $record) {
                        $record->canceled_at = Carbon::parse($record->cancels_at)->addDays(1);
                        $record->cancels_at = Carbon::parse($record->cancels_at)->addDays(1);
                        $record->ends_at = Carbon::parse($record->cancels_at)->addDays(1);
                        $record->save();
                        $record->renew();

                        Notification::make()
                            ->title('Renewed')
                            ->body('Subscription renewed successfully.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Delete')
                    ->iconButton(),
                Tables\Actions\ForceDeleteAction::make()
                    ->tooltip('Force Delete')
                    ->iconButton(),
                Tables\Actions\RestoreAction::make()
                    ->tooltip('Restore')
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
        ];
    }
}
