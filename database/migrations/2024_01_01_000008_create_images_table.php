<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('type')->nullable();
            $table->string('imageable_type');
            $table->unsignedBigInteger('imageable_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['imageable_type', 'imageable_id']);
        });

        // Migrate existing glyphs.image data into images table
        $glyphs = DB::table('glyphs')->whereNotNull('image')->where('image', '!=', '')->get();
        $now = now();
        foreach ($glyphs as $glyph) {
            DB::table('images')->insert([
                'path' => $glyph->image,
                'type' => 'glyph',
                'imageable_type' => 'App\\Models\\Glyph',
                'imageable_id' => $glyph->id,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Drop the image column from glyphs
        Schema::table('glyphs', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    public function down(): void
    {
        Schema::table('glyphs', function (Blueprint $table) {
            $table->string('image', 255)->nullable();
        });

        // Restore glyph images from images table
        $images = DB::table('images')
            ->where('imageable_type', 'App\\Models\\Glyph')
            ->where('type', 'glyph')
            ->get();

        foreach ($images as $image) {
            DB::table('glyphs')
                ->where('id', $image->imageable_id)
                ->update(['image' => $image->path]);
        }

        Schema::dropIfExists('images');
    }
};
