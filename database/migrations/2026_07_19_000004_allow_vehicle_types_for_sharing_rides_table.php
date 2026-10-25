<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AllowVehicleTypesForSharingRidesTable extends Migration
{
    public function up()
    {
        Schema::table('sharing_rides', function (Blueprint $table) {
            $table->dropForeign(['fleet_id']);
        });

        DB::statement('ALTER TABLE sharing_rides MODIFY fleet_id CHAR(36) NULL');

        Schema::table('sharing_rides', function (Blueprint $table) {
            $table->uuid('vehicle_type_id')->nullable()->after('fleet_id');

            $table->foreign('fleet_id')
                ->references('id')
                ->on('fleets')
                ->onDelete('cascade');

            $table->foreign('vehicle_type_id')
                ->references('id')
                ->on('vehicle_types')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('sharing_rides', function (Blueprint $table) {
            $table->dropForeign(['vehicle_type_id']);
            $table->dropColumn('vehicle_type_id');
        });
    }
}
