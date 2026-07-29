<?php

use App\Http\Controllers\Api\V1\Admin\EventController as AdminEventController;
use App\Http\Controllers\Api\V1\Admin\HallController;
use App\Http\Controllers\Api\V1\Admin\PerformanceController as AdminPerformanceController;
use App\Http\Controllers\Api\V1\Admin\SeatController;
use App\Http\Controllers\Api\V1\Admin\VenueController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PerformanceController;
use App\Http\Controllers\Api\V1\ReservationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{event}', [EventController::class, 'show']);
    Route::get('performances/{performance}', [PerformanceController::class, 'show']);
    Route::post('performances/{performance}/reservations', [ReservationController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders/{order}/confirm', [OrderController::class, 'confirm']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);

    Route::prefix('admin')->group(function (): void {
        Route::post('venues', [VenueController::class, 'store']);
        Route::post('venues/{venue}/halls', [HallController::class, 'store']);
        Route::post('halls/{hall}/seats', [SeatController::class, 'store']);
        Route::post('events', [AdminEventController::class, 'store']);
        Route::post('events/{event}/performances', [AdminPerformanceController::class, 'store']);
    });
});
