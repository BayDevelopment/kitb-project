<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SambutanBupati;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SambutanBupatiController extends Controller
{
    /**
     * Perbarui sambutan bupati.
     */
    public function update(Request $request): RedirectResponse
    {
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

        $validated = $request->validate([
            'nama_bupati' => [
                'required',
                'string',
                'max:255',
            ],

            'nama_bupati_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'nama_bupati_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'jabatan_bupati' => [
                'nullable',
                'string',
                'max:255',
            ],

            'jabatan_bupati_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'jabatan_bupati_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sambutan_bupati' => [
                'required',
                'string',
            ],

            'sambutan_bupati_en' => [
                'nullable',
                'string',
            ],

            'sambutan_bupati_zh' => [
                'nullable',
                'string',
            ],

            'foto_bupati' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],

            'status' => [
                'required',
                'boolean',
            ],

            'remove_foto_bupati' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Foto lama
        |--------------------------------------------------------------------------
        */

        $oldFoto = $sambutanBupati->foto_bupati;

        $photoMessage = null;

        /*
        |--------------------------------------------------------------------------
        | Upload foto baru
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('foto_bupati')) {
            $file = $request->file('foto_bupati');

            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            $filename = 'bupati-'
                . now()->format('YmdHis')
                . '-'
                . Str::lower(Str::random(12))
                . '.'
                . $extension;

            $newPath = $file->storeAs(
                'sambutan-bupati',
                $filename,
                'public'
            );

            if (! $newPath) {
                return back()->with('toast', [
                    'type' => 'error',
                    'message' => 'Foto bupati gagal disimpan. Silakan coba kembali.',
                ]);
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

            $sambutanBupati->foto_bupati = $newPath;

            $photoMessage = 'Foto bupati berhasil diperbarui.';
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus foto tanpa upload foto baru
        |--------------------------------------------------------------------------
        */

        if (
            ! $request->hasFile('foto_bupati')
            && $request->boolean('remove_foto_bupati')
            && $sambutanBupati->foto_bupati
        ) {
            if (
                Storage::disk('public')->exists(
                    $sambutanBupati->foto_bupati
                )
            ) {
                Storage::disk('public')->delete(
                    $sambutanBupati->foto_bupati
                );
            }

            $sambutanBupati->foto_bupati = null;

            $photoMessage = 'Foto bupati berhasil dihapus.';
        }

        /*
        |--------------------------------------------------------------------------
        | Update data tiga bahasa
        |--------------------------------------------------------------------------
        */

        $sambutanBupati->fill([
            'nama_bupati' => trim(
                $validated['nama_bupati']
            ),

            'nama_bupati_en' => trim(
                $validated['nama_bupati_en'] ?? ''
            ),

            'nama_bupati_zh' => trim(
                $validated['nama_bupati_zh'] ?? ''
            ),

            'jabatan_bupati' => (
                isset($validated['jabatan_bupati'])
                && $validated['jabatan_bupati'] !== null
                && trim($validated['jabatan_bupati']) !== ''
            )
                ? trim($validated['jabatan_bupati'])
                : null,

            'jabatan_bupati_en' => (
                isset($validated['jabatan_bupati_en'])
                && trim($validated['jabatan_bupati_en']) !== ''
            )
                ? trim($validated['jabatan_bupati_en'])
                : null,

            'jabatan_bupati_zh' => (
                isset($validated['jabatan_bupati_zh'])
                && trim($validated['jabatan_bupati_zh']) !== ''
            )
                ? trim($validated['jabatan_bupati_zh'])
                : null,

            'sambutan_bupati' => $this->sanitizeSambutan(
                $validated['sambutan_bupati']
            ),

            'sambutan_bupati_en' => $this->sanitizeSambutan(
                $validated['sambutan_bupati_en'] ?? ''
            ),

            'sambutan_bupati_zh' => $this->sanitizeSambutan(
                $validated['sambutan_bupati_zh'] ?? ''
            ),

            'status' => (bool) $validated['status'],
        ]);

        $sambutanBupati->save();

        /*
        |--------------------------------------------------------------------------
        | Toast
        |--------------------------------------------------------------------------
        */

        $message = $photoMessage
            ? 'Sambutan bupati berhasil diperbarui. ' . $photoMessage
            : 'Sambutan bupati berhasil diperbarui.';

        return back()->with('toast', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    /**
     * Sanitasi HTML rich text.
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
        | Hapus seluruh attribute dari tag
        |--------------------------------------------------------------------------
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
