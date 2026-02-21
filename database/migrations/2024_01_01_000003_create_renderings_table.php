<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renderings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('glyph_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renderings');
    }
};
