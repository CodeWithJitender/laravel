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
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('theme_mode', 20)->default('dark');
            $table->string('primary_color', 10)->default('#0c75a4');
            $table->string('secondary_color', 10)->default('#50535a');
            $table->string('accent_color', 10)->default('#0284c7');
            $table->string('bg_light', 10)->default('#f8fafc');
            $table->string('bg_dark', 10)->default('#090d16');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'theme_mode',
                'primary_color',
                'secondary_color',
                'accent_color',
                'bg_light',
                'bg_dark',
            ]);
        });
    }
};
