<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBattleRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('battle_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('challenger_id')->unsigned();
            $table->foreign('challenger_id')->references('id')->on('users');
            $table->integer('challenged_id')->unsigned();
            $table->foreign('challenged_id')->references('id')->on('users');
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
        Schema::drop('battle_requests');
    }
}
