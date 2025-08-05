<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\XenditPaymentController;

Route::prefix('api')->group(function () {
    Route::post('/payments/xendit/create', [XenditPaymentController::class, 'createInvoice']);
    Route::post('/payments/xendit/callback', [XenditPaymentController::class, 'handleCallback']);
});
