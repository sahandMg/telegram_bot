<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCacheDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cache_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->string('username');
            $table->tinyInteger('plan_id')->nullable();
            $table->string('service')->nullable();
            $table->boolean('closed')->default(0);
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
        Schema::dropIfExists('cache_data');
    }
}
