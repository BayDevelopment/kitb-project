<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
     * Tampilkan pengaturan sambutan direktur.
     */
    public function index(): Response
    {
        $sambutanDirektur = SambutanDirektur::query()->firstOrCreate(
            ['id' => 1],
            [
                'nama_direktur' => '',
                'jabatan_direktur' => 'Direktur',
                'sambutan_direktur' => '',
                'status' => false,
            ]
        );

        return Inertia::render(
            'admin/ProfilPerusahaan/SambutanDirektur',
            [
                'sambutanDirektur' => $sambutanDirektur,
            ]
        );
    }

    /**
     * Perbarui sambutan direktur.
     */
    public function update(Request $request): RedirectResponse
    {
        $sambutanDirektur = SambutanDirektur::query()->firstOrCreate(
            ['id' => 1],
            [
                'nama_direktur' => '',
                'jabatan_direktur' => 'Direktur',
                'sambutan_direktur' => '',
                'status' => false,
            ]
        );

        $validated = $request->validate([
            'nama_direktur' => [
                'required',
                'string',
                'max:255',
            ],

            'jabatan_direktur' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sambutan_direktur' => [
                'required',
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
        | Upload Foto Baru
        |--------------------------------------------------------------------------
        |
        | Remove.bg hanya sebagai enhancement.
        |
        | Jika:
        | - API berhasil       → PNG transparan
        | - quota habis        → foto asli
        | - API error          → foto asli
        | - timeout            → foto asli
        | - API key kosong     → foto asli
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
                        $response->successful() &&
                        $response->body()
                    ) {
                        $filename =
                            'direktur-' .
                            now()->format('YmdHis') .
                            '-' .
                            Str::lower(Str::random(12)) .
                            '.png';

                        $newPath =
                            'sambutan-direktur/' . $filename;

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
                    | Jangan gagalkan upload jika API bermasalah.
                    |--------------------------------------------------------------------------
                    */
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Fallback: Foto Asli
            |--------------------------------------------------------------------------
            */

            if (! $newPath) {
                $extension = strtolower(
                    $file->getClientOriginalExtension()
                );

                $filename =
                    'direktur-' .
                    now()->format('YmdHis') .
                    '-' .
                    Str::lower(Str::random(12)) .
                    '.' .
                    $extension;

                $newPath = $file->storeAs(
                    'sambutan-direktur',
                    $filename,
                    'public'
                );

                if (! $newPath) {
                    return back()->with('toast', [
                        'type' => 'error',
                        'message' =>
                        'Foto direktur gagal disimpan. Silakan coba kembali.',
                    ]);
                }

                $toastType = 'warning';

                $photoMessage = $apiKey
                    ? 'Background removal tidak tersedia atau kuota telah habis. Foto tetap disimpan dengan background asli.'
                    : 'Remove background belum dikonfigurasi. Foto tetap disimpan dengan background asli.';
            }

            /*
            |--------------------------------------------------------------------------
            | Hapus Foto Lama
            |--------------------------------------------------------------------------
            |
            | Baru dilakukan setelah foto baru berhasil tersimpan.
            |
            */

            if (
                $oldFoto &&
                Storage::disk('public')->exists($oldFoto)
            ) {
                Storage::disk('public')->delete($oldFoto);
            }

            $sambutanDirektur->foto_direktur = $newPath;

            /*
            |--------------------------------------------------------------------------
            | Pesan Remove.bg Berhasil
            |--------------------------------------------------------------------------
            */

            if ($backgroundRemoved) {
                $photoMessage =
                    'Foto berhasil diproses dan background telah dihapus.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus Foto Lama
        |--------------------------------------------------------------------------
        |
        | Hanya jika tidak ada upload foto baru.
        |
        */

        if (
            ! $request->hasFile('foto_direktur') &&
            $request->boolean('remove_foto_direktur') &&
            $sambutanDirektur->foto_direktur
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
        | Update Data
        |--------------------------------------------------------------------------
        */

        $sambutanDirektur->fill([
            'nama_direktur' =>
            trim($validated['nama_direktur']),

            'jabatan_direktur' =>
            $validated['jabatan_direktur']
                ? trim($validated['jabatan_direktur'])
                : null,

            'sambutan_direktur' =>
            $this->sanitizeSambutan(
                $validated['sambutan_direktur']
            ),

            'status' =>
            (bool) $validated['status'],
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

        $html = strip_tags(
            $html,
            '<' . implode('><', $allowedTags) . '>'
        );

        /*
        |--------------------------------------------------------------------------
        | Hapus seluruh attribute dari tag yang diizinkan.
        |--------------------------------------------------------------------------
        |
        | Contoh:
        | <p onclick="..."> → <p>
        |
        | Jadi editor tidak bisa menyimpan event handler
        | atau attribute HTML berbahaya.
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

        /*
        |--------------------------------------------------------------------------
        | Rapikan whitespace berlebihan
        |--------------------------------------------------------------------------
        */

        return trim($html ?? '');
    }
}
