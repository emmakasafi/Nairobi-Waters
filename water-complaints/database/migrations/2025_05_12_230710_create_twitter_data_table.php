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
        Schema::create('twitter_data', function (Blueprint $table) {
            $table->string('tweet_id')->primary();
            $table->text('text');
            $table->timestamp('created_at')->useCurrent();
            $table->string('user_handle')->nullable();
            $table->float('sentiment_score')->nullable();
            $table->enum('sentiment_label', ['positive', 'negative', 'neutral'])->nullable();
            $table->text('keywords')->nullable();
            $table->enum('language', ['en', 'sw', 'other'])->default('en');
            $table->string('location')->nullable();
            $table->enum('category', ['shortage', 'quality', 'billing', 'infrastructure', 'other'])->nullable();

            // Indexes for performance
            $table->index('created_at', 'idx_created_at');
            $table->index('sentiment_label', 'idx_sentiment_label');
            $table->index('category', 'idx_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('twitter_data');
    }
};