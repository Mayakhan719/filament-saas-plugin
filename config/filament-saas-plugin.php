<?php

return [
    "drivers" => [
        \Maya\FilamentSaasPlugin\Services\Drivers\Paypal::class,
        \Maya\FilamentSaasPlugin\Services\Drivers\StripeV3::class,
    ],
    "path" => "Maya\FilamentSaasPlugin\\Services\\Drivers",
    "notify" => [
        "to_database" => true,
    ],
    "user_model" => \App\Models\User::class,
];
