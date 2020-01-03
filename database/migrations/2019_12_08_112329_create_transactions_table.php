<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trans_id');
            $table->string('user_id')->unique();
            $table->tinyInteger('plan_id');
            $table->string('amount');
            $table->string('authority');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('username');
            $table->string('service');
            $table->string('status')->default('unpaid');
            $table->unsignedInteger('account_id')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
