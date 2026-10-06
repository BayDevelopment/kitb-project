<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->text('tentang_kami_en')
                ->nullable()
                ->after('tentang_kami');

            $table->text('tentang_kami_zh')
                ->nullable()
                ->after('tentang_kami_en');

            $table->text('latar_belakang_en')
                ->nullable()
                ->after('latar_belakang');

            $table->text('latar_belakang_zh')
                ->nullable()
                ->after('latar_belakang_en');

            $table->string('moto_en')
                ->nullable()
                ->after('moto');

            $table->string('moto_zh')
                ->nullable()
                ->after('moto_en');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'tentang_kami_en',
                'tentang_kami_zh',
                'latar_belakang_en',
                'latar_belakang_zh',
                'moto_en',
                'moto_zh',
            ]);
        });
    }
};
