<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCashbackCodesTable extends Migration
{
    public function up()
    {
        Schema::create('payment_cashback_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('user_id');
            $table->string('ride_id');
            $table->string('ride_type')->default('normal');
            $table->string('code')->unique();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status')->default('active'); // active, used, expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedInteger('verified_by')->nullable();
            $table->timestamps();

            $table->unique(['ride_id', 'ride_type']);
            $table->index('code');
            $table->index('user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_cashback_codes');
    }
}
