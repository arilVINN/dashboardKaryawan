<?php

use App\Http\Controllers\Kadiv\KadivDashboardController;
use App\Http\Controllers\Kadiv\KadivMessageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/kadiv')->group(function (): void {
    Route::get('/dashboard', [KadivDashboardController::class, 'index']);
    Route::get('/pesan', [KadivMessageController::class, 'index']);
    Route::post('/pesan', [KadivMessageController::class, 'store']);
});
