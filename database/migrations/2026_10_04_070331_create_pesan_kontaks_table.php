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
        Schema::create('pesan_kontaks', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email');
            $table->string('telepon', 30)->nullable();
            $table->string('perusahaan')->nullable();
            $table->string('subjek');
            $table->text('pesan');
            $table->string('status')->default('baru'); // baru, dibaca, dibalas
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesan_kontaks');
    }
};
