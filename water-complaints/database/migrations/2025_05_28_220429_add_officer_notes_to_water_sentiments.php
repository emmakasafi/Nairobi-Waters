<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfficerNotesToWaterSentiments extends Migration
{
    public function up()
    {
        Schema::table('water_sentiments', function (Blueprint $table) {
            $table->text('officer_notes')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('water_sentiments', function (Blueprint $table) {
            $table->dropColumn('officer_notes');
        });
    }
}