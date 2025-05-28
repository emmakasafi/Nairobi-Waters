<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusUpdatesTable extends Migration
{
    public function up()
    {
        Schema::create('status_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('water_sentiment_id')->constrained()->onDelete('cascade');
            $table->foreignId('officer_id')->constrained('users')->onDelete('cascade'); // Reference the users table
            $table->string('old_status');
            $table->string('new_status');
            $table->text('officer_notes')->nullable();
            $table->boolean('requires_customer_confirmation')->default(false);
            $table->string('status')->default('pending_confirmation');
            $table->timestamp('customer_confirmed_at')->nullable();
            $table->text('customer_rejection_reason')->nullable();
            $table->timestamp('customer_responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('status_updates');
    }
}