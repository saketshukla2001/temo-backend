<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharingRidesTable extends Migration
{
    public function up()
    {
        Schema::create('sharing_rides', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fleet_id');
            $table->unsignedInteger('user_id');
            $table->decimal('price_per_km', 10, 2);
            $table->unsignedInteger('total_capacity')->default(1);
            $table->boolean('active')->default(true);
            $table->string('company_key')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fleet_id')
                ->references('id')
                ->on('fleets')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sharing_rides');
    }
}
