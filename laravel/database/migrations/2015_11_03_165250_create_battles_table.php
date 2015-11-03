<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBattlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('battles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rapper1_id')->unsigned();
            $table->foreign('rapper1_id')->references('id')->on('users');
            $table->integer('rapper2_id')->unsigned();
            $table->foreign('rapper2_id')->references('id')->on('users');
            $table->string('video');
            $table->integer('votes_rapper1')->unsigned()->default(0);
            $table->integer('votes_rapper2')->unsigned()->default(0);
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
        Schema::drop('battles');
    }
}
