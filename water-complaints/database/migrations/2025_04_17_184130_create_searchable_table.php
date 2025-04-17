<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchableTable extends Migration
{
    public function up()
    {
        Schema::create('searchable', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('searchable');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('searchable');
    }
}