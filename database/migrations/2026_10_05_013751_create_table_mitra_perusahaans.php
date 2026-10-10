
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mitra_perusahaans', function (Blueprint $table) {
            $table->id();

            // Nama perusahaan dalam tiga bahasa
            $table->string('nama_perusahaan');
            $table->string('nama_perusahaan_en')->nullable();
            $table->string('nama_perusahaan_zh')->nullable();

            // Informasi perusahaan
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();

            // Pengaturan tampilan
            $table->boolean('aktif')->default(true);
            $table->unsignedInteger('urutan')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_perusahaans');
    }
};
