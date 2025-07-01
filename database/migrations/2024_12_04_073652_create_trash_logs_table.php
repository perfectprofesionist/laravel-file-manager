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
        Schema::create('trashlogs', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->uuid('guid')->unique(); // Unique GUID for identification
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade'); // Reference to the contents table
            $table->foreignId('user_id')->constrained('users'); // Reference to the user who trashed the folder
            $table->timestamp('trashed_at'); // Timestamp when the folder was trashed
            $table->timestamp('deleted_at')->nullable(); // Timestamp for permanent deletion (null if not deleted)
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trashlogs');
    }
};
