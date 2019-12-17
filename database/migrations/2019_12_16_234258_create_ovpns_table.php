<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOvpnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ovpns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->unsignedInteger('plan_id');
            $table->foreign('plan_id')->references('id')->on('plans');
            $table->boolean('used')->default(0);
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ovpns');
    }
}
