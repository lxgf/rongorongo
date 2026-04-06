<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('glyphs', function (Blueprint $table) {
            $table->string('meaning', 255)->nullable()->after('description');
            $table->enum('meaning_status', ['confirmed', 'proposed'])->nullable()->after('meaning');
            $table->string('meaning_source', 500)->nullable()->after('meaning_status');
        });
    }

    public function down(): void
    {
        Schema::table('glyphs', function (Blueprint $table) {
            $table->dropColumn(['meaning', 'meaning_status', 'meaning_source']);
        });
    }
};
