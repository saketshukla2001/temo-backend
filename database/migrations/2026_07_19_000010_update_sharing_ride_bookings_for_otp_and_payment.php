<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSharingRideBookingsForOtpAndPayment extends Migration
{
    public function up()
    {
        Schema::table('sharing_ride_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('sharing_ride_bookings', 'otp')) {
                $table->string('otp')->nullable()->after('seats');
            }
            if (!Schema::hasColumn('sharing_ride_bookings', 'otp_verified_at')) {
                $table->timestamp('otp_verified_at')->nullable()->after('otp');
            }
            if (!Schema::hasColumn('sharing_ride_bookings', 'payment_transaction_id')) {
                $table->string('payment_transaction_id')->nullable()->after('otp_verified_at');
            }
            if (!Schema::hasColumn('sharing_ride_bookings', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('payment_transaction_id');
            }
        });
    }

    public function down()
    {
        Schema::table('sharing_ride_bookings', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_verified_at', 'payment_transaction_id', 'payment_status']);
        });
    }
}
