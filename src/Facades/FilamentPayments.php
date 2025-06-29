<?php

namespace Maya\FilamentSaasPlugin\Facades;

use Illuminate\Support\Facades\Facade;
use Maya\FilamentSaasPlugin\Services\Contracts\PaymentRequest;

/**
 * @method static void loadDrivers()
 * @method static mixed pay(PaymentRequest $data, bool $json=false)
 */
class FilamentPayments extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'filament-payments';
    }
}
