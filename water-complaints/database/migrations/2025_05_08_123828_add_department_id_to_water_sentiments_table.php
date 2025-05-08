<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('water_sentiments', function (Blueprint $table) {
        $table->unsignedBigInteger('department_id')->nullable()->after('id');
        // If you have a departments table:
        // $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('water_sentiments', function (Blueprint $table) {
        $table->dropColumn('department_id');
    });
}

};
