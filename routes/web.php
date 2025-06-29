<?php

use Illuminate\Support\Facades\Route;
use Maya\FilamentSaasPlugin\Http\Controllers\PaymentController;
use Maya\FilamentSaasPlugin\Http\Middleware\VerifyBillableIsSubscribed;
use Maya\FilamentSaasPlugin\Livewire\PaymentProcess;

Route::name('payment.')->prefix('pay')->withoutMiddleware([VerifyBillableIsSubscribed::class])->group(function () {
    Route::get('{trx}', PaymentProcess::class)->name('index');
    Route::get('{trx}/cancel', [PaymentController::class, 'cancel'])->name('cancel');
    Route::post('initiate', [PaymentController::class, 'initiate'])->name('initiate');
    Route::get('info', [PaymentController::class, 'info'])->name('info');
});
Route::any('pay/callback/{gateway}', [PaymentController::class, 'verify'])->name('payments.callback');
