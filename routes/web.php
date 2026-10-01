<?php

use App\Http\Controllers\Admin\AnakUsahaController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\EaseOfDoingBusinessController;
use App\Http\Controllers\Admin\GaleriController;
use App\Http\Controllers\Admin\Kawasan\FasilitasController;
use App\Http\Controllers\Admin\Kawasan\InfrastrukturController;
use App\Http\Controllers\Admin\KunjunganLahanController;
use App\Http\Controllers\Admin\LowonganController;
use App\Http\Controllers\Admin\PeluangInvestasiController as AdminPeluangInvestasiController;
use App\Http\Controllers\Admin\PetaKawasanController;
use App\Http\Controllers\Admin\ProfilKawasanController;
use App\Http\Controllers\Admin\ProfilPerusahaanController;
use App\Http\Controllers\Admin\RuteController;
use App\Http\Controllers\Admin\StrukturPerusahaanController;
use App\Http\Controllers\Admin\VisiMisiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicLowonganKerjaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Index');
})->name('home');


// Karir public
Route::get('/karier', [PublicLowonganKerjaController::class, 'index'])->name('karier');
Route::get('/karier/{slug}', [PublicLowonganKerjaController::class, 'show'])->name('karier.detail');


// Admin
Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('dashboard', DashboardController::class)->name('dashboard');

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
    |--------------------------------------------------------------------------
    | Visi
    |--------------------------------------------------------------------------
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
    |--------------------------------------------------------------------------
    | Misi
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Profil Perusahaan - Struktur Perusahaan
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Hubungan Investor - Peluang Investasi
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/hubungan-investor/peluang-investasi',
        [AdminPeluangInvestasiController::class, 'index']
    )->name('hubungan-investor.peluang-investasi');

    Route::post(
        '/hubungan-investor/peluang-investasi',
        [AdminPeluangInvestasiController::class, 'store']
    )->name('hubungan-investor.peluang-investasi.store');

    Route::put(
        '/hubungan-investor/peluang-investasi/{peluangInvestasi}',
        [AdminPeluangInvestasiController::class, 'update']
    )->name('hubungan-investor.peluang-investasi.update');

    Route::delete(
        '/hubungan-investor/peluang-investasi/{peluangInvestasi}',
        [AdminPeluangInvestasiController::class, 'destroy']
    )->name('hubungan-investor.peluang-investasi.destroy');

    Route::patch(
        '/hubungan-investor/peluang-investasi/{peluangInvestasi}/toggle-aktif',
        [AdminPeluangInvestasiController::class, 'toggleAktif']
    )->name('hubungan-investor.peluang-investasi.toggle-aktif');

    Route::patch(
        '/hubungan-investor/peluang-investasi/{peluangInvestasi}/move',
        [AdminPeluangInvestasiController::class, 'move']
    )->name('hubungan-investor.peluang-investasi.move');

    /*
    |--------------------------------------------------------------------------
    | Hubungan Investor - Ease of Doing Business
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/hubungan-investor/ease-of-doing-business',
        [EaseOfDoingBusinessController::class, 'index']
    )->name('hubungan-investor.ease-of-doing-business');

    Route::post(
        '/hubungan-investor/ease-of-doing-business',
        [EaseOfDoingBusinessController::class, 'store']
    )->name('hubungan-investor.ease-of-doing-business.store');

    Route::put(
        '/hubungan-investor/ease-of-doing-business/{easeOfDoingBusiness}',
        [EaseOfDoingBusinessController::class, 'update']
    )->name('hubungan-investor.ease-of-doing-business.update');

    Route::delete(
        '/hubungan-investor/ease-of-doing-business/{easeOfDoingBusiness}',
        [EaseOfDoingBusinessController::class, 'destroy']
    )->name('hubungan-investor.ease-of-doing-business.destroy');

    Route::patch(
        '/hubungan-investor/ease-of-doing-business/{easeOfDoingBusiness}/toggle-aktif',
        [EaseOfDoingBusinessController::class, 'toggleAktif']
    )->name('hubungan-investor.ease-of-doing-business.toggle-aktif');

    Route::patch(
        '/hubungan-investor/ease-of-doing-business/{easeOfDoingBusiness}/move-up',
        [EaseOfDoingBusinessController::class, 'moveUp']
    )->name('hubungan-investor.ease-of-doing-business.move-up');

    Route::patch(
        '/hubungan-investor/ease-of-doing-business/{easeOfDoingBusiness}/move-down',
        [EaseOfDoingBusinessController::class, 'moveDown']
    )->name('hubungan-investor.ease-of-doing-business.move-down');

    /*
    |--------------------------------------------------------------------------
    | Hubungan Investor - Kunjungan Lahan
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/hubungan-investor/kunjungan-lahan',
        [KunjunganLahanController::class, 'index']
    )->name('hubungan-investor.kunjungan-lahan');

    Route::post(
        '/hubungan-investor/kunjungan-lahan',
        [KunjunganLahanController::class, 'store']
    )->name('hubungan-investor.kunjungan-lahan.store');

    Route::put(
        '/hubungan-investor/kunjungan-lahan/{kunjunganLahan}',
        [KunjunganLahanController::class, 'update']
    )->name('hubungan-investor.kunjungan-lahan.update');

    Route::delete(
        '/hubungan-investor/kunjungan-lahan/{kunjunganLahan}',
        [KunjunganLahanController::class, 'destroy']
    )->name('hubungan-investor.kunjungan-lahan.destroy');

    Route::patch(
        '/hubungan-investor/kunjungan-lahan/{kunjunganLahan}/approve',
        [KunjunganLahanController::class, 'approve']
    )->name('hubungan-investor.kunjungan-lahan.approve');

    Route::patch(
        '/hubungan-investor/kunjungan-lahan/{kunjunganLahan}/reject',
        [KunjunganLahanController::class, 'reject']
    )->name('hubungan-investor.kunjungan-lahan.reject');

    Route::patch(
        '/hubungan-investor/kunjungan-lahan/{kunjunganLahan}/complete',
        [KunjunganLahanController::class, 'complete']
    )->name('hubungan-investor.kunjungan-lahan.complete');

    Route::patch(
        '/hubungan-investor/kunjungan-lahan/{kunjunganLahan}/cancel',
        [KunjunganLahanController::class, 'cancel']
    )->name('hubungan-investor.kunjungan-lahan.cancel');

    /*
    |--------------------------------------------------------------------------
    | Kawasan - Profil Kawasan
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/kawasan/profil-kawasan',
        [ProfilKawasanController::class, 'index']
    )->name('kawasan.profil-kawasan');

    Route::post(
        '/kawasan/profil-kawasan',
        [ProfilKawasanController::class, 'store']
    )->name('kawasan.profil-kawasan.store');

    Route::put(
        '/kawasan/profil-kawasan/{profilKawasan}',
        [ProfilKawasanController::class, 'update']
    )->name('kawasan.profil-kawasan.update');

    Route::delete(
        '/kawasan/profil-kawasan/{profilKawasan}',
        [ProfilKawasanController::class, 'destroy']
    )->name('kawasan.profil-kawasan.destroy');

    Route::patch(
        '/kawasan/profil-kawasan/{profilKawasan}/toggle-aktif',
        [ProfilKawasanController::class, 'toggleAktif']
    )->name('kawasan.profil-kawasan.toggle-aktif');

    /*
    |--------------------------------------------------------------------------
    | Kawasan - Infrastruktur
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/kawasan/infrastruktur',
        [InfrastrukturController::class, 'index']
    )->name('kawasan.infrastruktur');

    Route::post(
        '/kawasan/infrastruktur',
        [InfrastrukturController::class, 'store']
    )->name('kawasan.infrastruktur.store');

    Route::put(
        '/kawasan/infrastruktur/{infrastruktur}',
        [InfrastrukturController::class, 'update']
    )->name('kawasan.infrastruktur.update');

    Route::delete(
        '/kawasan/infrastruktur/{infrastruktur}',
        [InfrastrukturController::class, 'destroy']
    )->name('kawasan.infrastruktur.destroy');

    Route::patch(
        '/kawasan/infrastruktur/{infrastruktur}/toggle-aktif',
        [InfrastrukturController::class, 'toggleAktif']
    )->name('kawasan.infrastruktur.toggle-aktif');

    Route::patch(
        '/kawasan/infrastruktur/{infrastruktur}/move-up',
        [InfrastrukturController::class, 'moveUp']
    )->name('kawasan.infrastruktur.move-up');

    Route::patch(
        '/kawasan/infrastruktur/{infrastruktur}/move-down',
        [InfrastrukturController::class, 'moveDown']
    )->name('kawasan.infrastruktur.move-down');

    /*
    |--------------------------------------------------------------------------
    | Kawasan - Fasilitas
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/kawasan/fasilitas',
        [FasilitasController::class, 'index']
    )->name('kawasan.fasilitas');

    Route::post(
        '/kawasan/fasilitas',
        [FasilitasController::class, 'store']
    )->name('kawasan.fasilitas.store');

    Route::put(
        '/kawasan/fasilitas/{fasilitas}',
        [FasilitasController::class, 'update']
    )->name('kawasan.fasilitas.update');

    Route::delete(
        '/kawasan/fasilitas/{fasilitas}',
        [FasilitasController::class, 'destroy']
    )->name('kawasan.fasilitas.destroy');

    Route::patch(
        '/kawasan/fasilitas/{fasilitas}/toggle-aktif',
        [FasilitasController::class, 'toggleAktif']
    )->name('kawasan.fasilitas.toggle-aktif');

    Route::patch(
        '/kawasan/fasilitas/{fasilitas}/move-up',
        [FasilitasController::class, 'moveUp']
    )->name('kawasan.fasilitas.move-up');

    Route::patch(
        '/kawasan/fasilitas/{fasilitas}/move-down',
        [FasilitasController::class, 'moveDown']
    )->name('kawasan.fasilitas.move-down');

    /*
    |--------------------------------------------------------------------------
    | Kawasan - Peta Kawasan
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/kawasan/peta-kawasan',
        [PetaKawasanController::class, 'index']
    )->name('kawasan.peta-kawasan');

    Route::post(
        '/kawasan/peta-kawasan',
        [PetaKawasanController::class, 'store']
    )->name('kawasan.peta-kawasan.store');

    Route::put(
        '/kawasan/peta-kawasan/{petaKawasan}',
        [PetaKawasanController::class, 'update']
    )->name('kawasan.peta-kawasan.update');

    Route::delete(
        '/kawasan/peta-kawasan/{petaKawasan}',
        [PetaKawasanController::class, 'destroy']
    )->name('kawasan.peta-kawasan.destroy');

    Route::patch(
        '/kawasan/peta-kawasan/{petaKawasan}/toggle-aktif',
        [PetaKawasanController::class, 'toggleAktif']
    )->name('kawasan.peta-kawasan.toggle-aktif');

    Route::patch(
        '/kawasan/peta-kawasan/{petaKawasan}/move-up',
        [PetaKawasanController::class, 'moveUp']
    )->name('kawasan.peta-kawasan.move-up');

    Route::patch(
        '/kawasan/peta-kawasan/{petaKawasan}/move-down',
        [PetaKawasanController::class, 'moveDown']
    )->name('kawasan.peta-kawasan.move-down');

    /*
|--------------------------------------------------------------------------
| Hubungan Investor - Rute Pelayaran & Lokasi
|--------------------------------------------------------------------------
*/

    Route::get(
        '/hubungan-investor/rute-pelayaran-lokasi',
        [RuteController::class, 'index']
    )->name('hubungan-investor.rute-pelayaran-lokasi');

    Route::post(
        '/hubungan-investor/rute-pelayaran-lokasi',
        [RuteController::class, 'store']
    )->name('hubungan-investor.rute-pelayaran-lokasi.store');

    Route::put(
        '/hubungan-investor/rute-pelayaran-lokasi/{rute}',
        [RuteController::class, 'update']
    )->name('hubungan-investor.rute-pelayaran-lokasi.update');

    Route::delete(
        '/hubungan-investor/rute-pelayaran-lokasi/{rute}',
        [RuteController::class, 'destroy']
    )->name('hubungan-investor.rute-pelayaran-lokasi.destroy');

    Route::patch(
        '/hubungan-investor/rute-pelayaran-lokasi/{rute}/toggle-aktif',
        [RuteController::class, 'toggleAktif']
    )->name('hubungan-investor.rute-pelayaran-lokasi.toggle-aktif');

    Route::patch(
        '/hubungan-investor/rute-pelayaran-lokasi/{rute}/move',
        [RuteController::class, 'move']
    )->name('hubungan-investor.rute-pelayaran-lokasi.move');

    Route::get(
        '/pusat-informasi/berita',
        [BeritaController::class, 'index']
    )->name('pusat-informasi.berita');

    Route::post(
        '/pusat-informasi/berita',
        [BeritaController::class, 'store']
    )->name('pusat-informasi.berita.store');

    Route::put(
        '/pusat-informasi/berita/{berita}',
        [BeritaController::class, 'update']
    )->name('pusat-informasi.berita.update');

    Route::delete(
        '/pusat-informasi/berita/{berita}',
        [BeritaController::class, 'destroy']
    )->name('pusat-informasi.berita.destroy');

    Route::patch(
        '/pusat-informasi/berita/{berita}/toggle-status',
        [BeritaController::class, 'toggleStatus']
    )->name('pusat-informasi.berita.toggle-status');

    Route::patch(
        '/pusat-informasi/berita/{berita}/toggle-featured',
        [BeritaController::class, 'toggleFeatured']
    )->name('pusat-informasi.berita.toggle-featured');

    Route::get(
        '/pusat-informasi/galeri',
        [GaleriController::class, 'index']
    )->name('pusat-informasi.galeri.index');

    Route::post(
        '/pusat-informasi/galeri',
        [GaleriController::class, 'store']
    )->name('pusat-informasi.galeri.store');

    Route::put(
        '/pusat-informasi/galeri/{galeri}',
        [GaleriController::class, 'update']
    )->name('pusat-informasi.galeri.update');

    Route::delete(
        '/pusat-informasi/galeri/{galeri}',
        [GaleriController::class, 'destroy']
    )->name('pusat-informasi.galeri.destroy');

    Route::patch(
        '/pusat-informasi/galeri/{galeri}/toggle-aktif',
        [GaleriController::class, 'toggleAktif']
    )->name('pusat-informasi.galeri.toggle-aktif');

    Route::patch(
        '/pusat-informasi/galeri/{galeri}/move',
        [GaleriController::class, 'move']
    )->name('pusat-informasi.galeri.move');

    /*
|--------------------------------------------------------------------------
| Pusat Informasi - Lowongan
|--------------------------------------------------------------------------
*/

    Route::get(
        '/pusat-informasi/lowongan',
        [LowonganController::class, 'index']
    )->name('pusat-informasi.lowongan.index');

    Route::post(
        '/pusat-informasi/lowongan',
        [LowonganController::class, 'store']
    )->name('pusat-informasi.lowongan.store');

    Route::put(
        '/pusat-informasi/lowongan/{lowongan}',
        [LowonganController::class, 'update']
    )->name('pusat-informasi.lowongan.update');

    Route::delete(
        '/pusat-informasi/lowongan/{lowongan}',
        [LowonganController::class, 'destroy']
    )->name('pusat-informasi.lowongan.destroy');

    Route::patch(
        '/pusat-informasi/lowongan/{lowongan}/toggle-status',
        [LowonganController::class, 'toggleStatus']
    )->name('pusat-informasi.lowongan.toggle-status');

    Route::patch(
        '/pusat-informasi/lowongan/{lowongan}/toggle-featured',
        [LowonganController::class, 'toggleFeatured']
    )->name('pusat-informasi.lowongan.toggle-featured');

    Route::patch(
        '/pusat-informasi/lowongan/{lowongan}/move',
        [LowonganController::class, 'move']
    )->name('pusat-informasi.lowongan.move');
});

require __DIR__ . '/settings.php';
