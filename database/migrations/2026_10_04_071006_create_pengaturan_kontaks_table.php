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
        Schema::create('pengaturan_kontaks', function (Blueprint $table) {
            $table->id();

            // Identitas & alamat
            $table->string('nama_perusahaan')->nullable();
            $table->text('alamat')->nullable();

            // Kontak
            $table->string('telepon', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('email_investor', 150)->nullable();

            // Jam operasional
            $table->string('jam_operasional')->nullable(); // mis. "Senin - Jumat, 08.00 - 16.00 WIB"

            // Peta
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('maps_embed_url')->nullable();

            // Media sosial
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('youtube')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_kontaks');
    }
};
