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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->uuid('guid')->unique(); // Unique GUID for identification
            $table->string('name');
            $table->boolean('is_folder')->default(false); // Differentiate folder (true) or file (false)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('contents')->onDelete('cascade'); // Recursive parent
            $table->string('path')->nullable(); // Nullable for folders
            $table->bigInteger('size')->nullable(); // Nullable for folders
            $table->string('extension')->nullable(); // Nullable for folders
            $table->softDeletes(); // Soft deletes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
