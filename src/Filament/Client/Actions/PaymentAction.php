<?php

namespace Maya\FilamentSaasPlugin\Filament\Actions;

use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Maya\FilamentSaasPlugin\Facades\FilamentPayments;
use Maya\FilamentSaasPlugin\Services\Contracts\PaymentRequest;

class PaymentAction extends Action
{
    public ?\Closure $request = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requiresConfirmation();

        $this->icon('heroicon-s-credit-card');
        $this->iconButton();
        $this->color("info");
        $this->label('Make Payment');
        $this->tooltip('Make Payment');
    }

    public function request(\Closure $request): static
    {
        $this->request = $request;
        return $this;
    }

    public function pay(): static
    {
        $this->action(function () {
            $callback = call_user_func($this->request, $this->record);
            $payment = FilamentPayments::pay(data: $callback);
            if (is_array($payment)) {
                Notification::make()
                    ->title('Error')
                    ->body($payment['error'])
                    ->danger()
                    ->send();
            } else {
                return redirect()->to($payment);
            }
        });

        return $this;
    }
}
