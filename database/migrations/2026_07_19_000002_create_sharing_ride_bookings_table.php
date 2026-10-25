<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharingRideBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('sharing_ride_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sharing_ride_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('seats')->default(1);
            $table->string('otp')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sharing_ride_id')
                ->references('id')
                ->on('sharing_rides')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sharing_ride_bookings');
    }
}
