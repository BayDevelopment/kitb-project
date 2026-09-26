<?php

use App\Http\Controllers\Admin\AnakUsahaController;
use App\Http\Controllers\Admin\ProfilPerusahaanController;
use App\Http\Controllers\Admin\VisiMisiController;
use App\Http\Controllers\Admin\StrukturPerusahaanController;
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

    Route::get(
        '/profil-perusahaan/struktur-perusahaan',
        [StrukturPerusahaanController::class, 'index']
    )->name('profil-perusahaan.struktur-perusahaan');

    Route::post(
        '/profil-perusahaan/struktur-perusahaan',
        [StrukturPerusahaanController::class, 'store']
    )->name('profil-perusahaan.struktur-perusahaan.store');

    Route::put(
        '/profil-perusahaan/struktur-perusahaan/{strukturPerusahaan}',
        [StrukturPerusahaanController::class, 'update']
    )->name('profil-perusahaan.struktur-perusahaan.update');

    Route::delete(
        '/profil-perusahaan/struktur-perusahaan/{strukturPerusahaan}',
        [StrukturPerusahaanController::class, 'destroy']
    )->name('profil-perusahaan.struktur-perusahaan.destroy');

    Route::patch(
        '/profil-perusahaan/struktur-perusahaan/{strukturPerusahaan}/toggle-aktif',
        [StrukturPerusahaanController::class, 'toggleAktif']
    )->name('profil-perusahaan.struktur-perusahaan.toggle-aktif');

    Route::patch(
        '/profil-perusahaan/struktur-perusahaan/{strukturPerusahaan}/move',
        [StrukturPerusahaanController::class, 'move']
    )->name('profil-perusahaan.struktur-perusahaan.move');

    /*
|--------------------------------------------------------------------------
| Profil Perusahaan - Anak Usaha
|--------------------------------------------------------------------------
*/

    Route::get(
        '/profil-perusahaan/anak-usaha',
        [AnakUsahaController::class, 'index']
    )->name('profil-perusahaan.anak-usaha');

    Route::post(
        '/profil-perusahaan/anak-usaha',
        [AnakUsahaController::class, 'store']
    )->name('profil-perusahaan.anak-usaha.store');

    Route::put(
        '/profil-perusahaan/anak-usaha/{anakUsaha}',
        [AnakUsahaController::class, 'update']
    )->name('profil-perusahaan.anak-usaha.update');

    Route::delete(
        '/profil-perusahaan/anak-usaha/{anakUsaha}',
        [AnakUsahaController::class, 'destroy']
    )->name('profil-perusahaan.anak-usaha.destroy');

    Route::patch(
        '/profil-perusahaan/anak-usaha/{anakUsaha}/toggle-aktif',
        [AnakUsahaController::class, 'toggleAktif']
    )->name('profil-perusahaan.anak-usaha.toggle-aktif');

    Route::patch(
        '/profil-perusahaan/anak-usaha/{anakUsaha}/move',
        [AnakUsahaController::class, 'move']
    )->name('profil-perusahaan.anak-usaha.move');
});

require __DIR__ . '/settings.php';
