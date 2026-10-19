<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/* Route::get('/', function () {
    return view('welcome');
}); */

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');


Route::resource('clients', ClientController::class);
