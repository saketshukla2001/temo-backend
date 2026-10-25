<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlanIdToDriverSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::table('driver_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_subscriptions', 'plan_id')) {
                $table->uuid('plan_id')->nullable()->after('driver_id');
            }
            if (!Schema::hasColumn('driver_subscriptions', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('transaction_id');
            }
        });
    }

    public function down()
    {
        Schema::table('driver_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['plan_id', 'payment_status']);
        });
    }
}
