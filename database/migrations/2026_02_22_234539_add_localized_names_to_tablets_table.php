<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tablets', function (Blueprint $table) {
            $table->string('name_en', 100)->nullable()->after('name');
            $table->string('name_ru', 100)->nullable()->after('name_en');
            $table->string('type', 30)->nullable()->after('name_ru');
        });
    }

    public function down(): void
    {
        Schema::table('tablets', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ru', 'type']);
        });
    }
};
