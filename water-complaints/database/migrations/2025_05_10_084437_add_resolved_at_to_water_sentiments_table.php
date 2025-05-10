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
        $table->timestamp('resolved_at')->nullable()->after('assigned_at');
    });
}

public function down()
{
    Schema::table('water_sentiments', function (Blueprint $table) {
        $table->dropColumn('resolved_at');
    });
}
};
