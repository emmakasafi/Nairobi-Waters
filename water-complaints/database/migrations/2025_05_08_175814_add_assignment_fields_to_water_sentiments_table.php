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
        
        
        $table->unsignedBigInteger('assigned_by')->nullable()->after('department_id');
        $table->text('admin_notes')->nullable()->after('assigned_by');
        $table->timestamp('assigned_at')->nullable()->after('admin_notes');

      
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('water_sentiments', function (Blueprint $table) {
            //
        });
    }
};
