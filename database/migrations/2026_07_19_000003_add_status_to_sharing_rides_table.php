<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToSharingRidesTable extends Migration
{
    public function up()
    {
        Schema::table('sharing_rides', function (Blueprint $table) {
            $table->string('status')->default('ongoing')->after('total_capacity');
            $table->timestamp('completed_at')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->text('cancel_reason')->nullable()->after('cancelled_at');
        });
    }

    public function down()
    {
        Schema::table('sharing_rides', function (Blueprint $table) {
            $table->dropColumn(['status', 'completed_at', 'cancelled_at', 'cancel_reason']);
        });
    }
}
