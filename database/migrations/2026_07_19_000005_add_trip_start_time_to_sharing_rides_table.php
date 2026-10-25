<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTripStartTimeToSharingRidesTable extends Migration
{
    public function up()
    {
        Schema::table('sharing_rides', function (Blueprint $table) {
            $table->timestamp('trip_start_time')->nullable()->after('total_capacity');
        });
    }

    public function down()
    {
        Schema::table('sharing_rides', function (Blueprint $table) {
            $table->dropColumn('trip_start_time');
        });
    }
}
