<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tablet_renderings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tablet_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rendering_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('compound_glyph_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('position');
            $table->boolean('is_inverted')->default(false);
            $table->boolean('is_mirrored')->default(false);
            $table->boolean('is_small')->default(false);
            $table->boolean('is_enlarged')->default(false);
            $table->boolean('is_truncated')->default(false);
            $table->boolean('is_distorted')->default(false);
            $table->boolean('is_uncertain')->default(false);
            $table->boolean('is_nonstandard')->default(false);
            $table->timestamps();

            $table->unique(['tablet_line_id', 'position']);
            $table->index('rendering_id');
            $table->index('compound_glyph_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tablet_renderings');
    }
};
