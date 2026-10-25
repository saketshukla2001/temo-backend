<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExtendDriverSubscriptionsTable extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE driver_subscriptions MODIFY transaction_id VARCHAR(255) NULL');
        DB::statement("ALTER TABLE driver_subscriptions MODIFY subscription_type ENUM('monthly','yearly') NULL");
        DB::statement('ALTER TABLE driver_subscriptions MODIFY expired_at TIMESTAMP NULL');

        Schema::table('driver_subscriptions', function (Blueprint $table) {
            $table->enum('subscription_basis', ['day_wise', 'ride_wise'])->default('day_wise')->after('subscription_type');
            $table->unsignedInteger('duration_days')->nullable()->after('paid_amount');
            $table->unsignedInteger('total_rides')->nullable()->after('duration_days');
            $table->unsignedInteger('used_rides')->default(0)->after('total_rides');
            $table->unsignedInteger('remaining_rides')->nullable()->after('used_rides');
            $table->timestamp('started_at')->nullable()->after('remaining_rides');
        });
    }

    public function down()
    {
        Schema::table('driver_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_basis',
                'duration_days',
                'total_rides',
                'used_rides',
                'remaining_rides',
                'started_at',
            ]);
        });
    }
}
