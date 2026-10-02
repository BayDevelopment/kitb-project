<?php

namespace App\Http\Controllers;

use App\Models\KunjunganLahan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PublicKunjunganLahanController extends Controller
{
    /**
     * Form pengajuan kunjungan.
     *
     * Jika ?tanggal=YYYY-MM-DD dikirim, ikut mengirim jadwal
     * yang sudah terisi pada tanggal tersebut (hanya jam, tanpa data pemohon).
     */
    public function index(Request $request): Response
    {
        $tanggal = (string) $request->input('tanggal', '');

        $jadwalTerisi = [];

        if (
            $tanggal !== ''
            && Carbon::canBeCreatedFromFormat($tanggal, 'Y-m-d')
        ) {
            $jadwalTerisi = KunjunganLahan::query()
                ->whereDate('tanggal_kunjungan', $tanggal)
                ->whereIn('status', [
                    KunjunganLahan::STATUS_PENDING,
                    KunjunganLahan::STATUS_DISETUJUI,
                ])
                ->orderBy('waktu_mulai')
                ->get(['waktu_mulai', 'waktu_selesai'])
                ->map(fn($item) => [
                    'mulai' => substr((string) $item->waktu_mulai, 0, 5),
                    'selesai' => substr((string) $item->waktu_selesai, 0, 5),
                ])
                ->values();
        }

        return Inertia::render('AjukanKunjungan/Index', [
            'jadwal_terisi' => $jadwalTerisi,
            'nomor_registrasi' => session('nomor_registrasi'),
        ]);
    }

    /**
     * Menyimpan pengajuan dari pengunjung.
     *
     * Status, nomor registrasi, dan catatan admin selalu ditentukan server.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'min:3', 'max:150'],
            'instansi' => ['nullable', 'string', 'max:200'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'telepon' => ['required', 'string', 'max:25'],
            'tanggal_kunjungan' => ['required', 'date', 'after_or_equal:today'],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_selesai' => ['required', 'date_format:H:i', 'after:waktu_mulai'],
            'jumlah_peserta' => ['required', 'integer', 'min:1', 'max:65535'],
            'area_lahan' => ['required', 'string', 'max:255'],
            'keperluan' => ['nullable', 'string', 'max:5000'],
            'memerlukan_pendamping' => ['required', 'boolean'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'telepon.required' => 'Nomor telepon wajib diisi.',
            'tanggal_kunjungan.required' => 'Tanggal kunjungan wajib diisi.',
            'tanggal_kunjungan.after_or_equal' => 'Tanggal kunjungan tidak boleh sebelum hari ini.',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi.',
            'waktu_selesai.required' => 'Waktu selesai wajib diisi.',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
            'jumlah_peserta.min' => 'Jumlah peserta minimal 1 orang.',
            'area_lahan.required' => 'Area lahan yang dikunjungi wajib diisi.',
        ]);

        $validated['memerlukan_pendamping'] = $request->boolean('memerlukan_pendamping');

        try {
            $nomor = DB::transaction(function () use ($validated) {
                // Cek bentrok di dalam transaksi + lock agar dua request
                // bersamaan tidak sama-sama lolos.
                $conflict = KunjunganLahan::query()
                    ->whereDate('tanggal_kunjungan', $validated['tanggal_kunjungan'])
                    ->whereIn('status', [
                        KunjunganLahan::STATUS_PENDING,
                        KunjunganLahan::STATUS_DISETUJUI,
                    ])
                    ->where('waktu_mulai', '<', $validated['waktu_selesai'])
                    ->where('waktu_selesai', '>', $validated['waktu_mulai'])
                    ->lockForUpdate()
                    ->exists();

                if ($conflict) {
                    return null;
                }

                $nomor = $this->generateRegistrationNumber();

                KunjunganLahan::create([
                    ...$validated,
                    'nomor_registrasi' => $nomor,
                    'status' => KunjunganLahan::STATUS_PENDING,
                    'catatan_admin' => null,
                    'disetujui_at' => null,
                    'ditolak_at' => null,
                    'selesai_at' => null,
                ]);

                return $nomor;
            });
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Pengajuan gagal dikirim. Silakan coba kembali.',
                ]);
        }

        if ($nomor === null) {
            return back()->withErrors([
                'waktu_mulai' => 'Jadwal pada waktu tersebut sudah terisi. Pilih waktu lain.',
            ]);
        }

        return redirect()
            ->route('ajukan-kunjungan.index')
            ->with('nomor_registrasi', $nomor);
    }

    /**
     * Format: KITB-VISIT-YYYYMMDD-0001
     */
    private function generateRegistrationNumber(): string
    {
        $prefix = 'KITB-VISIT-' . now()->format('Ymd') . '-';

        $last = KunjunganLahan::query()
            ->where('nomor_registrasi', 'like', "{$prefix}%")
            ->orderByDesc('nomor_registrasi')
            ->value('nomor_registrasi');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
