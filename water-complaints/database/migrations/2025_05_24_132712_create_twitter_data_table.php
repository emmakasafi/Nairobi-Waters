<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwitterDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_data', function (Blueprint $table) {
            $table->id();
            $table->text('original_caption');
            $table->text('processed_caption');
            $table->timestamp('timestamp');
            $table->string('overall_sentiment', 10);
            $table->string('complaint_category', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twitter_data');
    }
}