<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compound_glyph_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compound_glyph_id')->constrained()->cascadeOnDelete();
            $table->foreignId('glyph_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('order');

            $table->unique(['compound_glyph_id', 'order']);
            $table->index('glyph_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compound_glyph_parts');
    }
};
