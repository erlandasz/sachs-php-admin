<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('printing_settings', function (Blueprint $table) {
            $table->boolean('is_default')->default(false);

            $table->boolean('has_colors')->default(false);

            $table->tinyInteger('all_days_r')->default(0);
            $table->tinyInteger('all_days_g')->default(0);
            $table->tinyInteger('all_days_b')->default(0);

            $table->tinyInteger('days_1_and_2_r')->default(0);
            $table->tinyInteger('days_1_and_2_g')->default(0);
            $table->tinyInteger('days_1_and_2_b')->default(0);

            $table->tinyInteger('days_2_and_3_r')->default(0);
            $table->tinyInteger('days_2_and_3_g')->default(0);
            $table->tinyInteger('days_2_and_3_b')->default(0);

            $table->tinyInteger('day_1_r')->default(0);
            $table->tinyInteger('day_1_g')->default(0);
            $table->tinyInteger('day_1_b')->default(0);

            $table->tinyInteger('day_2_r')->default(0);
            $table->tinyInteger('day_2_g')->default(0);
            $table->tinyInteger('day_2_b')->default(0);

            $table->tinyInteger('day_3_r')->default(0);
            $table->tinyInteger('day_3_g')->default(0);
            $table->tinyInteger('day_3_b')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printing_settings', function (Blueprint $table) {
            //
        });
    }
};
