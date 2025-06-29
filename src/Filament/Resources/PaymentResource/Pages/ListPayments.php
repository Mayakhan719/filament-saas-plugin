<?php

namespace Maya\FilamentSaasPlugin\Filament\Resources\PaymentResource\Pages;

use Maya\FilamentSaasPlugin\Filament\Pages\PaymentGateway;
use Maya\FilamentSaasPlugin\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('payment')
                ->url(PaymentGateway::getUrl())
                ->label('Payment Gateways')
                ->tooltip('Payment Gateways')
                ->icon('heroicon-o-cog')
                ->hiddenLabel(),
        ];
    }
}
