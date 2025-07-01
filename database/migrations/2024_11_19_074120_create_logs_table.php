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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name'); // Table affected
            $table->string('operation'); // e.g., 'created', 'updated', 'deleted'
            $table->text('old_data')->nullable(); // Previous data (for updates and deletes)
            $table->text('new_data')->nullable(); // New data (for updates and creates)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // User performing the action
            $table->timestamps();
            $table->softDeletes(); // Soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
