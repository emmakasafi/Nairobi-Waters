<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToWaterSentimentsAndStatusUpdates extends Migration
{
    public function up()
    {
        Schema::table('water_sentiments', function (Blueprint $table) {
            if (!Schema::hasColumn('water_sentiments', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('water_sentiments', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('water_sentiments', 'pending_status_update_id')) {
                $table->unsignedBigInteger('pending_status_update_id')->nullable()->after('status');
            }
        });

        Schema::table('status_updates', function (Blueprint $table) {
            if (!Schema::hasColumn('status_updates', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('new_status');
            }
            if (!Schema::hasColumn('status_updates', 'status')) {
                $table->string('status')->default('pending')->after('new_status');
            }
        });
    }

    public function down()
    {
        Schema::table('water_sentiments', function (Blueprint $table) {
            if (Schema::hasColumn('water_sentiments', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
            if (Schema::hasColumn('water_sentiments', 'resolved_at')) {
                $table->dropColumn('resolved_at');
            }
            if (Schema::hasColumn('water_sentiments', 'pending_status_update_id')) {
                $table->dropColumn('pending_status_update_id');
            }
        });

        Schema::table('status_updates', function (Blueprint $table) {
            if (Schema::hasColumn('status_updates', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('status_updates', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}