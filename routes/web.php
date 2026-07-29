<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\HallController;
use App\Http\Controllers\Admin\PerformanceController;
use App\Http\Controllers\Admin\SeatController;
use App\Http\Controllers\Admin\VenueController;
use App\Http\Controllers\EventPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventPageController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventPageController::class, 'show'])->name('events.show');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('venues', [VenueController::class, 'index'])->name('venues.index');
    Route::post('venues', [VenueController::class, 'store'])->name('venues.store');
    Route::post('venues/{venue}/halls', [HallController::class, 'store'])->name('halls.store');
    Route::get('halls/{hall}', [HallController::class, 'show'])->name('halls.show');
    Route::post('halls/{hall}/seats', [SeatController::class, 'store'])->name('seats.store');
    Route::get('events', [AdminEventController::class, 'index'])->name('events.index');
    Route::get('events/create', [AdminEventController::class, 'create'])->name('events.create');
    Route::post('events', [AdminEventController::class, 'store'])->name('events.store');
    Route::get('events/{event}', [AdminEventController::class, 'show'])->name('events.show');
    Route::get('events/{event}/edit', [AdminEventController::class, 'edit'])->name('events.edit');
    Route::put('events/{event}', [AdminEventController::class, 'update'])->name('events.update');
    Route::patch('events/{event}/publication', [AdminEventController::class, 'togglePublication'])->name('events.publication.toggle');
    Route::post('events/{event}/performances', [PerformanceController::class, 'store'])->name('performances.store');
});
