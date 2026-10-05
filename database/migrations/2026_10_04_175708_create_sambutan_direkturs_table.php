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
        Schema::create('sambutan_direkturs', function (Blueprint $table) {
            $table->id();

            $table->string('nama_direktur');
            $table->string('jabatan_direktur')->nullable();

            $table->text('sambutan_direktur');

            $table->string('foto_direktur')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sambutan_direkturs');
    }
};
