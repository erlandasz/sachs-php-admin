<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Setting name for reference');
            $table->enum('page_size', ['A6', 'A4', 'A5', 'A3', 'LETTER'])->default('A6');
            $table->enum('orientation', ['portrait', 'landscape'])->default('portrait');
            $table->json('page_dimensions')->nullable()->comment('Custom dimensions [width, height] in mm');
            $table->string('font_family')->default('Times')->comment('Font family name');
            $table->string('font_weight')->default('B')->comment('Font weight (e.g., B for bold)');
            $table->integer('base_font_size')->default(32)->comment('Base font size in points');
            $table->float('available_width_multiplier')->default(0.8)->comment('Multiplier for available page width (e.g., 0.8 = 80%)');
            $table->json('default_colors')->nullable()->comment('Default text and background colors');
            $table->float('row_padding')->default(8)->comment('Vertical padding between rows');
            $table->float('y_offset')->default(45)->comment('Initial Y offset for first text row');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printing_settings');
    }
};
