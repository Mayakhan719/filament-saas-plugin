<?php

namespace Maya\FilamentSaasPlugin\Filament\Resources;

use Maya\FilamentSaasPlugin\Filament\Resources\PaymentResource\Pages;
use Maya\FilamentSaasPlugin\Filament\Resources\PaymentResource\RelationManagers;
use Maya\FilamentSaasPlugin\Models\Payment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationGroup(): ?string
    {
        return 'Subscriptions';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trx')
                    ->label('Transaction ID')
                    ->sortable(),
                TextColumn::make('method_name')
                    ->label('Method Name')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(function (Payment $record) {
                        return Number::currency($record->amount, in: $record->method_currency) . " + " . Number::currency($record->charge, in: $record->method_currency) . '<br>' . Number::currency(($record->amount + $record->charge), in: $record->method_currency);
                    })->html(),

                TextColumn::make('rate')
                    ->label('Conversion')
                    ->formatStateUsing(function (Payment $record) {
                        return Number::currency(1, in: 'USD') . " = " . Number::currency($record->rate, in: $record->method_currency) . '<br>' . Number::currency($record->final_amount, in: 'USD');
                    })->html(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn($record) => match ($record->status) {
                        0 => 'Processing',
                        1 => 'Completed',
                        2 => 'Cancelled',
                        default => 'Initiated',
                    })
                    ->icon(fn($record) => match ($record->status) {
                        0 => 'heroicon-o-clock',
                        1 => 'heroicon-s-check-circle',
                        2 => 'heroicon-s-x-circle',
                        default => 'heroicon-s-x-circle',
                    })
                    ->color(fn($record) => match ($record->status) {
                        0 => 'info',
                        1 => 'success',
                        2 => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y h:iA')
                    ->description(fn($record): string => Carbon::parse($record->created_at)->diffForHumans()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        0 => 'Processing',
                        1 => 'Completed',
                        2 => 'Cancelled',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('status')
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->searchable();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Details')
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->state(fn($record) => match ($record->status) {
                                        0 => 'Processing',
                                        1 => 'Completed',
                                        2 => 'Cancelled',
                                        default => 'Initiated',
                                    })
                                    ->icon(fn($record) => match ($record->status) {
                                        0 => 'heroicon-o-clock',
                                        1 => 'heroicon-s-check-circle',
                                        2 => 'heroicon-s-x-circle',
                                        default => 'heroicon-s-x-circle',
                                    })
                                    ->color(fn($record) => match ($record->status) {
                                        0 => 'info',
                                        1 => 'success',
                                        2 => 'danger',
                                        default => 'secondary',
                                    }),
                                TextEntry::make('created_at')
                                    ->label('Date')
                                    ->dateTime(),
                                TextEntry::make('trx')
                                    ->label('Transaction Number'),
                                TextEntry::make('account.username')
                                    ->label('Username'),
                                TextEntry::make('method_name')
                                    ->label('Method Name'),
                                TextEntry::make('method_code')
                                    ->label('Method Code')
                                    ->formatStateUsing(fn($state) => \Illuminate\Support\Str::limit($state, 10)),
                                TextEntry::make('amount')
                                    ->label('Amount')
                                    ->money(fn($record) => $record->method_currency ?? 'USD', locale: 'en'),
                                TextEntry::make('charge')
                                    ->label('Charge')
                                    ->money(fn($record) => $record->method_currency ?? 'USD', locale: 'en'),
                                TextEntry::make('rate')
                                    ->label('Rate')
                                    ->formatStateUsing(fn(Payment $record) => Number::currency(1, in: 'USD') . " = " . Number::currency($record->rate, in: $record->method_currency))
                                    ->html(),
                                TextEntry::make('final_amount')
                                    ->label('After Rate Conversion')
                                    ->money(fn($record) => $record->method_currency ?? 'USD', locale: 'en'),
                            ])
                            ->columns(2),
                        Tab::make('Customer')
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('Name')
                                    ->formatStateUsing(fn(Payment $record) => $record->customer['name'] ?? 'N/A'),
                                TextEntry::make('customer.email')
                                    ->label('Email')
                                    ->formatStateUsing(fn(Payment $record) => $record->customer['email'] ?? 'N/A'),
                                TextEntry::make('customer.mobile')
                                    ->label('Mobile')
                                    ->formatStateUsing(fn(Payment $record) => $record->customer['mobile'] ?? 'N/A'),
                            ])
                            ->columns(2),
                        Tab::make('Shipping')
                            ->schema([
                                TextEntry::make('shipping_info.address_one')
                                    ->label('Address One')
                                    ->formatStateUsing(fn(Payment $record) => $record->shipping_info['address_one'] ?? 'N/A'),
                                TextEntry::make('shipping_info.address_two')
                                    ->label('Address Two')
                                    ->formatStateUsing(fn(Payment $record) => $record->shipping_info['address_two'] ?? 'N/A'),
                                TextEntry::make('shipping_info.area')
                                    ->label('Area')
                                    ->formatStateUsing(fn(Payment $record) => $record->shipping_info['area'] ?? 'N/A'),
                                TextEntry::make('shipping_info.city')
                                    ->label('City')
                                    ->formatStateUsing(fn(Payment $record) => $record->shipping_info['city'] ?? 'N/A'),
                                TextEntry::make('shipping_info.sub_city')
                                    ->label('Sub City')
                                    ->formatStateUsing(fn(Payment $record) => $record->shipping_info['sub_city'] ?? 'N/A'),
                                TextEntry::make('shipping_info.state')
                                    ->label('State')
                                    ->formatStateUsing(fn(Payment $record) => $record->shipping_info['state'] ?? 'N/A'),
                                TextEntry::make('shipping_info.postcode')
                                    ->label('Postcode')
                                    ->formatStateUsing(fn(Payment $record) => $record->shipping_info['postcode'] ?? 'N/A'),
                                TextEntry::make('shipping_info.country')
                                    ->label('Country')
                                    ->formatStateUsing(fn(Payment $record) => $record->shipping_info['country'] ?? 'N/A'),
                            ])
                            ->columns(2),
                        Tab::make('Billing')
                            ->schema([
                                TextEntry::make('billing_info.address_one')
                                    ->label('Address One')
                                    ->formatStateUsing(fn(Payment $record) => $record->billing_info['address_one'] ?? 'N/A'),
                                TextEntry::make('billing_info.address_two')
                                    ->label('Address Two')
                                    ->formatStateUsing(fn(Payment $record) => $record->billing_info['address_two'] ?? 'N/A'),
                                TextEntry::make('billing_info.area')
                                    ->label('Area')
                                    ->formatStateUsing(fn(Payment $record) => $record->billing_info['area'] ?? 'N/A'),
                                TextEntry::make('billing_info.city')
                                    ->label('City')
                                    ->formatStateUsing(fn(Payment $record) => $record->billing_info['city'] ?? 'N/A'),
                                TextEntry::make('billing_info.sub_city')
                                    ->label('Sub City')
                                    ->formatStateUsing(fn(Payment $record) => $record->billing_info['sub_city'] ?? 'N/A'),
                                TextEntry::make('billing_info.state')
                                    ->label('State')
                                    ->formatStateUsing(fn(Payment $record) => $record->billing_info['state'] ?? 'N/A'),
                                TextEntry::make('billing_info.postcode')
                                    ->label('Postcode')
                                    ->formatStateUsing(fn(Payment $record) => $record->billing_info['postcode'] ?? 'N/A'),
                                TextEntry::make('billing_info.country')
                                    ->label('Country')
                                    ->formatStateUsing(fn(Payment $record) => $record->billing_info['country'] ?? 'N/A'),
                            ])
                            ->columns(2),
                    ])
                    ->contained(false)
            ])
            ->columns(1);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
