<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverSubscriptionPlansTable extends Migration
{
    public function up()
    {
        Schema::create('driver_subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('subscription_basis', ['day_wise', 'ride_wise']);
            $table->double('price', 10, 2);
            $table->unsignedInteger('duration_days')->nullable();
            $table->unsignedInteger('total_rides')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_subscription_plans');
    }
}
