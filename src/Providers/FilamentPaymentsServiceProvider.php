<?php

namespace Maya\FilamentSaasPlugin\Providers;

use Maya\FilamentSaasPlugin\Events\ChangePlan;
use Maya\FilamentSaasPlugin\Events\RenewPlan;
use Maya\FilamentSaasPlugin\Events\SubscribePlan;
use Maya\FilamentSaasPlugin\Facades\FilamentNotify;
use Illuminate\Support\ServiceProvider;
use Maya\FilamentSaasPlugin\Facades\FilamentPayments;
use Maya\FilamentSaasPlugin\Services\Contracts\PaymentBillingInfo;
use Maya\FilamentSaasPlugin\Services\Contracts\PaymentCustomer;
use Maya\FilamentSaasPlugin\Services\Contracts\PaymentRequest;
use Maya\FilamentSaasPlugin\Services\Contracts\PaymentShippingInfo;
use Maya\FilamentSaasPlugin\Facades\FilamentSubscriptions;
use Maya\FilamentSaasPlugin\Filament\Client\Pages\PaymentSuccess;
use Maya\FilamentSaasPlugin\Models\Payment;
use Maya\FilamentSaasPlugin\Services\Contracts\Payload;
use Illuminate\Http\RedirectResponse;
use Laravelcm\Subscriptions\Models\Plan;

class FilamentPaymentsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('filament-payments', function () {
            return new \Maya\FilamentSaasPlugin\Services\FilamentPaymentsServices();
        });
    }

    /**
     * Bootstrap services.
     */


    public function boot(): void
    {
        FilamentSubscriptions::beforeSubscription(function ($data) {
            $this->PaymentPage($data, SubscribePlan::class);
        });

        FilamentSubscriptions::beforeRenew(function ($data) {
            $this->PaymentPage($data, RenewPlan::class);
        });
        FilamentSubscriptions::beforeChange(function ($data) {
            $this->PaymentPage($data, ChangePlan::class);
        });
        FilamentSubscriptions::afterSubscription(function ($data) {
            $this->afterSubscription();
        });
        FilamentSubscriptions::afterRenew(function ($data) {
            $this->afterRenew();
        });
        FilamentSubscriptions::afterChange(function ($data) {
            $this->afterChange();
        });
        
    }
    private function PaymentPage($data, $event)
    {
        return redirect()->to(
            FilamentPayments::pay(
                data: PaymentRequest::make(Plan::class)
                    ->model_id($data['new']->id)
                    ->team_id($data['team_id'])
                    ->event($event)
                    ->currency('USD')
                    ->amount($data['new']->price)
                    ->details('Subscription Payment')
                    ->success_url(url('/client'))
                    ->cancel_url(url('/client'))
                    ->customer(
                        PaymentCustomer::make('John Doe')
                            ->email('john@gmail.com')
                            ->mobile('+201207860084')
                    )
                    ->billing_info(
                        PaymentBillingInfo::make('123 Main St')
                            ->area('Downtown')
                            ->city('Cairo')
                            ->state('Cairo')
                            ->postcode('12345')
                            ->country('EG')
                    )
                    ->shipping_info(
                        PaymentShippingInfo::make('123 Main St')
                            ->area('Downtown')
                            ->city('Cairo')
                            ->state('Cairo')
                            ->postcode('12345')
                            ->country('EG')
                    )
            )
        );
    }
    private function afterSubscription()
    {
        FilamentNotify::notify(
            title: 'Subscription Successful',
            body: 'Your subscription was successfully created.',
            type: 'success',
            toDatabase: true,
            toToast: true
        );
    }

    private function afterRenew()
    {
        FilamentNotify::notify(
            title: 'Subscription Renewed',
            body: 'Your subscription was successfully renewed.',
            type: 'success',
            toDatabase: config('filament-saas-plugin.notify.to_database', true),
            toToast: true
        );
    }
    private function afterChange()
    {
        FilamentNotify::notify(
            title: 'Subscription Changed',
            body: 'Your subscription was successfully changed.',
            type: 'success',
            toDatabase: config('filament-saas-plugin.notify.to_database', true),
            toToast: true
        );
    }
}
