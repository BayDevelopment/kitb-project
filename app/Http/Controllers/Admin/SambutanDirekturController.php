<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SambutanBupati;
use App\Models\SambutanDirektur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SambutanDirekturController extends Controller
{
    /**
     * Tampilkan pengaturan sambutan Direktur dan Bupati.
     */
    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Sambutan Direktur
        |--------------------------------------------------------------------------
        */

        $sambutanDirektur = SambutanDirektur::query()->firstOrCreate(
            ['id' => 1],
            [
                'nama_direktur' => '',
                'nama_direktur_en' => '',
                'nama_direktur_zh' => '',

                'jabatan_direktur' => 'Direktur',
                'jabatan_direktur_en' => '',
                'jabatan_direktur_zh' => '',

                'sambutan_direktur' => '',
                'sambutan_direktur_en' => '',
                'sambutan_direktur_zh' => '',

                'status' => false,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Sambutan Bupati
        |--------------------------------------------------------------------------
        */

        $sambutanBupati = SambutanBupati::query()->firstOrCreate(
            ['id' => 1],
            [
                'nama_bupati' => '',
                'nama_bupati_en' => '',
                'nama_bupati_zh' => '',

                'jabatan_bupati' => 'Bupati',
                'jabatan_bupati_en' => '',
                'jabatan_bupati_zh' => '',

                'sambutan_bupati' => '',
                'sambutan_bupati_en' => '',
                'sambutan_bupati_zh' => '',

                'status' => false,
            ]
        );

        return Inertia::render(
            'admin/ProfilPerusahaan/SambutanDirektur',
            [
                'sambutanDirektur' => $sambutanDirektur,
                'sambutanBupati' => $sambutanBupati,
            ]
        );
    }

    /**
     * Perbarui sambutan Direktur.
     */
    public function update(Request $request): RedirectResponse
    {
        $sambutanDirektur = SambutanDirektur::query()->firstOrCreate(
            ['id' => 1],
            [
                'nama_direktur' => '',
                'nama_direktur_en' => '',
                'nama_direktur_zh' => '',

                'jabatan_direktur' => 'Direktur',
                'jabatan_direktur_en' => '',
                'jabatan_direktur_zh' => '',

                'sambutan_direktur' => '',
                'sambutan_direktur_en' => '',
                'sambutan_direktur_zh' => '',

                'status' => false,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'nama_direktur' => [
                'required',
                'string',
                'max:255',
            ],

            'nama_direktur_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'nama_direktur_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'jabatan_direktur' => [
                'nullable',
                'string',
                'max:255',
            ],

            'jabatan_direktur_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'jabatan_direktur_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sambutan_direktur' => [
                'required',
                'string',
            ],

            'sambutan_direktur_en' => [
                'nullable',
                'string',
            ],

            'sambutan_direktur_zh' => [
                'nullable',
                'string',
            ],

            'foto_direktur' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],

            'status' => [
                'required',
                'boolean',
            ],

            'remove_foto_direktur' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Foto lama
        |--------------------------------------------------------------------------
        */

        $oldFoto = $sambutanDirektur->foto_direktur;

        $toastType = 'success';
        $photoMessage = null;

        /*
        |--------------------------------------------------------------------------
        | Upload foto baru
        |--------------------------------------------------------------------------
        |
        | Remove.bg digunakan sebagai enhancement.
        | Jika API tidak tersedia/gagal, foto asli tetap disimpan.
        |
        */

        if ($request->hasFile('foto_direktur')) {
            $file = $request->file('foto_direktur');

            $apiKey = config('services.removebg.key');

            $newPath = null;
            $backgroundRemoved = false;

            /*
            |--------------------------------------------------------------------------
            | Coba Remove.bg
            |--------------------------------------------------------------------------
            */

            if ($apiKey) {
                try {
                    $response = Http::timeout(120)
                        ->retry(2, 1000)
                        ->withHeaders([
                            'X-Api-Key' => $apiKey,
                        ])
                        ->attach(
                            'image_file',
                            file_get_contents($file->getRealPath()),
                            $file->getClientOriginalName()
                        )
                        ->post(
                            'https://api.remove.bg/v1.0/removebg',
                            [
                                'size' => 'auto',
                                'format' => 'png',
                            ]
                        );

                    if (
                        $response->successful()
                        && $response->body()
                    ) {
                        $filename = 'direktur-'
                            . now()->format('YmdHis')
                            . '-'
                            . Str::lower(Str::random(12))
                            . '.png';

                        $newPath = 'sambutan-direktur/' . $filename;

                        $stored = Storage::disk('public')->put(
                            $newPath,
                            $response->body()
                        );

                        if ($stored) {
                            $backgroundRemoved = true;
                        } else {
                            $newPath = null;
                        }
                    }
                } catch (\Throwable $e) {
                    /*
                    |--------------------------------------------------------------------------
                    | Jangan gagalkan upload jika Remove.bg bermasalah.
                    |--------------------------------------------------------------------------
                    */
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Fallback: simpan foto asli
            |--------------------------------------------------------------------------
            */

            if (! $newPath) {
                $extension = strtolower(
                    $file->getClientOriginalExtension()
                );

                $filename = 'direktur-'
                    . now()->format('YmdHis')
                    . '-'
                    . Str::lower(Str::random(12))
                    . '.'
                    . $extension;

                $newPath = $file->storeAs(
                    'sambutan-direktur',
                    $filename,
                    'public'
                );

                if (! $newPath) {
                    return back()->with('toast', [
                        'type' => 'error',
                        'message' => 'Foto direktur gagal disimpan. Silakan coba kembali.',
                    ]);
                }

                $toastType = 'warning';

                $photoMessage = $apiKey
                    ? 'Background removal tidak tersedia atau kuota telah habis. Foto tetap disimpan dengan background asli.'
                    : 'Remove background belum dikonfigurasi. Foto tetap disimpan dengan background asli.';
            }

            /*
            |--------------------------------------------------------------------------
            | Hapus foto lama setelah foto baru berhasil disimpan
            |--------------------------------------------------------------------------
            */

            if (
                $oldFoto
                && Storage::disk('public')->exists($oldFoto)
            ) {
                Storage::disk('public')->delete($oldFoto);
            }

            $sambutanDirektur->foto_direktur = $newPath;

            /*
            |--------------------------------------------------------------------------
            | Pesan jika background berhasil dihapus
            |--------------------------------------------------------------------------
            */

            if ($backgroundRemoved) {
                $photoMessage =
                    'Foto berhasil diproses dan background telah dihapus.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus foto lama tanpa upload foto baru
        |--------------------------------------------------------------------------
        */

        if (
            ! $request->hasFile('foto_direktur')
            && $request->boolean('remove_foto_direktur')
            && $sambutanDirektur->foto_direktur
        ) {
            if (
                Storage::disk('public')->exists(
                    $sambutanDirektur->foto_direktur
                )
            ) {
                Storage::disk('public')->delete(
                    $sambutanDirektur->foto_direktur
                );
            }

            $sambutanDirektur->foto_direktur = null;

            $photoMessage = 'Foto direktur berhasil dihapus.';
        }

        /*
        |--------------------------------------------------------------------------
        | Update data tiga bahasa
        |--------------------------------------------------------------------------
        */

        $sambutanDirektur->fill([
            'nama_direktur' => trim(
                $validated['nama_direktur']
            ),

            'nama_direktur_en' => trim(
                $validated['nama_direktur_en'] ?? ''
            ),

            'nama_direktur_zh' => trim(
                $validated['nama_direktur_zh'] ?? ''
            ),

            'jabatan_direktur' => (
                isset($validated['jabatan_direktur'])
                && $validated['jabatan_direktur'] !== null
                && trim($validated['jabatan_direktur']) !== ''
            )
                ? trim($validated['jabatan_direktur'])
                : null,

            'jabatan_direktur_en' => (
                isset($validated['jabatan_direktur_en'])
                && trim($validated['jabatan_direktur_en']) !== ''
            )
                ? trim($validated['jabatan_direktur_en'])
                : null,

            'jabatan_direktur_zh' => (
                isset($validated['jabatan_direktur_zh'])
                && trim($validated['jabatan_direktur_zh']) !== ''
            )
                ? trim($validated['jabatan_direktur_zh'])
                : null,

            'sambutan_direktur' => $this->sanitizeSambutan(
                $validated['sambutan_direktur']
            ),

            'sambutan_direktur_en' => $this->sanitizeSambutan(
                $validated['sambutan_direktur_en'] ?? ''
            ),

            'sambutan_direktur_zh' => $this->sanitizeSambutan(
                $validated['sambutan_direktur_zh'] ?? ''
            ),

            'status' => (bool) $validated['status'],
        ]);

        $sambutanDirektur->save();

        /*
        |--------------------------------------------------------------------------
        | Toast
        |--------------------------------------------------------------------------
        */

        $message = $photoMessage
            ? 'Sambutan direktur berhasil diperbarui. ' . $photoMessage
            : 'Sambutan direktur berhasil diperbarui.';

        return back()->with('toast', [
            'type' => $toastType,
            'message' => $message,
        ]);
    }

    /**
     * Sanitasi HTML rich text.
     *
     * Hanya tag formatting yang diperlukan yang diperbolehkan.
     */
    private function sanitizeSambutan(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $allowedTags = [
            'p',
            'br',
            'strong',
            'b',
            'em',
            'i',
            'u',
            'ul',
            'ol',
            'li',
            'blockquote',
        ];

        /*
        |--------------------------------------------------------------------------
        | Hapus tag HTML yang tidak diperbolehkan
        |--------------------------------------------------------------------------
        */

        $html = strip_tags(
            $html,
            '<' . implode('><', $allowedTags) . '>'
        );

        /*
        |--------------------------------------------------------------------------
        | Hapus seluruh attribute dari tag yang diizinkan
        |--------------------------------------------------------------------------
        |
        | Contoh:
        | <p onclick="..."> menjadi <p>
        |
        */

        $html = preg_replace_callback(
            '/<\s*([a-z0-9]+)(?:\s+[^>]*)?>/i',
            function ($matches) use ($allowedTags) {
                $tag = strtolower($matches[1]);

                if (! in_array($tag, $allowedTags, true)) {
                    return '';
                }

                return '<' . $tag . '>';
            },
            $html
        );

        return trim($html ?? '');
    }
}
