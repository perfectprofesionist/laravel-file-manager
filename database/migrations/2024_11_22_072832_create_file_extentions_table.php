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
        Schema::create('file_extentions', function (Blueprint $table) {
            $table->id();
            $table->uuid('guid')->unique(); // Unique GUID for file identification
            $table->foreignId('file_type_id')->constrained('file_types');
            $table->string('name');
            $table->string('svg_path');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_extentions');
    }
};
