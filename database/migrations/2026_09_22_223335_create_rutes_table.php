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
        Schema::create('rutes', function (Blueprint $table) {
            $table->id();

            $table->string('nama_rute');
            $table->string('jalur');
            $table->decimal('jarak', 10, 1);
            $table->string('satuan_jarak')->default('km');
            $table->string('waktu_tempuh');
            $table->text('deskripsi')->nullable();
            $table->integer('urutan')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutes');
    }
};
