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
        Schema::create('test_post_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('test_posts')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('test_categories')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['post_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_post_category');
    }
};