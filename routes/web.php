<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EventPageController;
use App\Http\Controllers\TicketPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventPageController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventPageController::class, 'show'])->name('events.show');
Route::get('/checkout/{performance}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::get('/tickets', [TicketPageController::class, 'index'])->name('tickets.index');
Route::post('/tickets/lookup', [TicketPageController::class, 'lookup'])->name('tickets.lookup');
Route::get('/tickets/{order}', [TicketPageController::class, 'show'])->middleware('signed')->name('tickets.show');
