<?php

use App\Http\Controllers\Admin\ProfilPerusahaanController;
use App\Http\Controllers\Admin\VisiMisiController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Index');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::inertia('dashboard', 'Dashboard')
        ->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Profil Perusahaan - Tentang Kami
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Profil Perusahaan - Visi & Misi
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profil-perusahaan/visi-misi',
        [VisiMisiController::class, 'index']
    )->name('profil-perusahaan.visi-misi');

    /*
     * Visi
     */

    Route::post(
        '/profil-perusahaan/visi-misi/visi',
        [VisiMisiController::class, 'storeVisi']
    )->name('profil-perusahaan.visi-misi.visi.store');

    Route::put(
        '/profil-perusahaan/visi-misi/visi/{visi}',
        [VisiMisiController::class, 'updateVisi']
    )->name('profil-perusahaan.visi-misi.visi.update');

    /*
     * Misi
     */

    Route::post(
        '/profil-perusahaan/visi-misi/{visi}/misi',
        [VisiMisiController::class, 'storeMisi']
    )->name('profil-perusahaan.visi-misi.misi.store');

    Route::put(
        '/profil-perusahaan/visi-misi/{visi}/misi/{misi}',
        [VisiMisiController::class, 'updateMisi']
    )->name('profil-perusahaan.visi-misi.misi.update');

    Route::delete(
        '/profil-perusahaan/visi-misi/{visi}/misi/{misi}',
        [VisiMisiController::class, 'destroyMisi']
    )->name('profil-perusahaan.visi-misi.misi.destroy');

    Route::patch(
        '/profil-perusahaan/visi-misi/{visi}/misi/{misi}/move',
        [VisiMisiController::class, 'moveMisi']
    )->name('profil-perusahaan.visi-misi.misi.move');
});

require __DIR__ . '/settings.php';
