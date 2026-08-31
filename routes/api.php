<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhooks\SteadfastWebhookController;

Route::post('/webhooks/steadfast/delivery-status', [SteadfastWebhookController::class, 'handle'])
    ->name('webhooks.steadfast.delivery');

Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});
