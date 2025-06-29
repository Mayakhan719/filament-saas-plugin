<?php

namespace Maya\FilamentSaasPlugin\Filament\Pages;

use Maya\FilamentSaasPlugin\Facades\FilamentPayments;
use Maya\FilamentSaasPlugin\Filament\Resources\PaymentResource;
use Maya\FilamentSaasPlugin\Models\PaymentGateway as PaymentGatewayModel;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\BooleanColumn;

class PaymentGateway extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament-saas-plugin::filament.pages.payment-gateway';
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    public array $data = [];
    protected ?string $status = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    public function mount(): void
    {
        FilamentPayments::loadDrivers();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->action(fn() => redirect()->to(PaymentResource::getUrl('index')))
                ->color('danger')
                ->label('Back to Payments'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(PaymentGatewayModel::query())
            ->paginated(false)
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('alias')->label('Alias'),
                ToggleColumn::make('status')->label('Status'),
                BooleanColumn::make('crypto')->label('Crypto'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->tooltip('Edit')
                    ->icon('heroicon-s-pencil')
                    ->iconButton()
                    ->form([
                        SpatieMediaLibraryFileUpload::make('image')
                            ->label('Image')
                            ->collection('image')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        KeyValue::make('gateway_parameters')
                            ->label('Gateway Parameters')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->editableKeys(false)
                            ->addable(false)
                            ->deletable(false),
                        Repeater::make('supported_currencies')
                            ->reorderable(false)
                            ->label('Supported Currencies')
                            ->schema([
                                TextInput::make('currency')->columnSpanFull()->label('Currency'),
                                TextInput::make('symbol')->label('Symbol'),
                                TextInput::make('rate')->label('Rate')->required(),
                                TextInput::make('minimum_amount')->label('Minimum Amount')->required(),
                                TextInput::make('maximum_amount')->label('Maximum Amount')->required(),
                                TextInput::make('fixed_charge')->label('Fixed Charge')->required(),
                                TextInput::make('percent_charge')->label('Percent Charge')->required(),
                            ])
                            ->columns(3),
                    ])
                    ->fillForm(fn($record) => $record->toArray())
                    ->action(function (array $data, $record) {
                        $record->update($data);
                        Notification::make()
                            ->title('Gateway Updated')
                            ->body('Gateway has been updated successfully')
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->searchable();
    }
}
