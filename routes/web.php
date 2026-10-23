<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentification
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [
        AuthenticatedSessionController::class,
        'create',
    ])->name('login');

    Route::post('/connexion', [
        AuthenticatedSessionController::class,
        'store',
    ])->name('login.store');
});

Route::post('/deconnexion', [
    AuthenticatedSessionController::class,
    'destroy',
])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Pages protégées du CRM
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('clients', ClientController::class);

    Route::resource('produits', ProduitController::class)
        ->except(['show']);

    Route::resource('categories', CategorieController::class)
        ->except(['show']);

    Route::resource('commandes', CommandeController::class);

    Route::post('/commandes/{commande}/paiements', [
        PaiementController::class,
        'store',
    ])->name('commandes.paiements.store');

    Route::get('/paiements', [
        PaiementController::class,
        'index',
    ])->name('paiements.index');

    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profil', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::put('/profil/mot-de-passe', [
        ProfileController::class,
        'updatePassword',
    ])->name('profile.password');
});
