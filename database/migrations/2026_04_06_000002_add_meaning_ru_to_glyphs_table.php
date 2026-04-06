<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('glyphs', function (Blueprint $table) {
            $table->string('meaning_ru', 255)->nullable()->after('meaning_source');
            $table->string('meaning_source_ru', 500)->nullable()->after('meaning_ru');
        });
    }

    public function down(): void
    {
        Schema::table('glyphs', function (Blueprint $table) {
            $table->dropColumn(['meaning_ru', 'meaning_source_ru']);
        });
    }
};
