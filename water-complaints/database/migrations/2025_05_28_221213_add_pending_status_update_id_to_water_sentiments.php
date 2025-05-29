<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPendingStatusUpdateIdToWaterSentiments extends Migration
{
    public function up()
    {
        Schema::table('water_sentiments', function (Blueprint $table) {
            $table->unsignedBigInteger('pending_status_update_id')->nullable()->after('officer_notes');
            $table->foreign('pending_status_update_id')->references('id')->on('status_updates')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('water_sentiments', function (Blueprint $table) {
            $table->dropForeign(['pending_status_update_id']);
            $table->dropColumn('pending_status_update_id');
        });
    }
}