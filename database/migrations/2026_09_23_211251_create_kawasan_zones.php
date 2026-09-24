<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kawasan_zones', function (Blueprint $table) {
            $table->id();

            // Opsional: zona menempel ke satu peta. Peta tanpa zona = tidak ada baris di sini.
            $table->foreignId('master_plan_id')->nullable()
                ->constrained('master_plans')->cascadeOnDelete();

            $table->string('kode', 30);                     // phase-1, supporting, port
            $table->string('label');                        // "Phase 1", "Supporting", "Port"
            $table->decimal('luas_ha', 10, 2);              // luas dalam hektar
            $table->string('warna', 9)->default('#14895f'); // hex, sesuai warna di peta
            $table->string('catatan')->nullable();          // mis. "Joint Venture"
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            // "kode" cukup unik per peta, bukan global.
            $table->unique(['master_plan_id', 'kode']);
            $table->index(['aktif', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kawasan_zones');
    }
};
