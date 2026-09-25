<?php

use App\Http\Controllers\Admin\ProfilPerusahaanController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Index');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::inertia('dashboard', 'Admin/Dashboard')
        ->name('dashboard');

    Route::get(
        '/profil-perusahaan/tentang-kami',
        [ProfilPerusahaanController::class, 'tentangKami']
    )->name('profil-perusahaan.tentang-kami');

    Route::post(
        '/profil-perusahaan/tentang-kami',
        [ProfilPerusahaanController::class, 'store']
    )->name('profil-perusahaan.tentang-kami.store');

    Route::put(
        '/profil-perusahaan/tentang-kami/{companyProfile}',
        [ProfilPerusahaanController::class, 'update']
    )->name('profil-perusahaan.tentang-kami.update');

    Route::delete(
        '/profil-perusahaan/tentang-kami/{companyProfile}',
        [ProfilPerusahaanController::class, 'destroy']
    )->name('profil-perusahaan.tentang-kami.destroy');
});

require __DIR__ . '/settings.php';
