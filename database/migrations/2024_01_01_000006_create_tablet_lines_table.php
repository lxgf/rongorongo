<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tablet_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tablet_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('side'); // 0 = recto, 1 = verso
            $table->unsignedTinyInteger('line');
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr');
            $table->timestamps();

            $table->unique(['tablet_id', 'side', 'line']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tablet_lines');
    }
};
