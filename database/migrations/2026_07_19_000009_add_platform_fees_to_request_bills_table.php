<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlatformFeesToRequestBillsTable extends Migration
{
    public function up()
    {
        Schema::table('request_bills', function (Blueprint $table) {
            $table->double('platform_fee_for_user', 10, 2)->default(0)->after('admin_commision_from_driver');
            $table->double('platform_fee_for_driver', 10, 2)->default(0)->after('platform_fee_for_user');
        });
    }

    public function down()
    {
        Schema::table('request_bills', function (Blueprint $table) {
            $table->dropColumn(['platform_fee_for_user', 'platform_fee_for_driver']);
        });
    }
}
