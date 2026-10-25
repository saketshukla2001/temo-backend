<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatformFeesTable extends Migration
{
    public function up()
    {
        Schema::create('platform_fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('user_type', ['user', 'driver']);
            $table->enum('fee_type', ['fixed', 'percentage'])->default('fixed');
            $table->double('amount', 10, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('platform_fees');
    }
}
