<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenBattlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('open_battles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rapper1_id')->unsigned();
            $table->foreign('rapper1_id')->references('id')->on('users');
            $table->integer('rapper2_id')->unsigned();
            $table->foreign('rapper2_id')->references('id')->on('users');
            $table->tinyInteger('phase')->unsigned()->default(1);
            $table->timestamp('phase_start');
            $table->tinyInteger('beat1_id')->unsigned()->nullable();
            $table->string('rapper1_round1')->nullable();
            $table->string('rapper2_round2')->nullable();
            $table->tinyInteger('beat2_id')->unsigned()->nullable();
            $table->string('rapper2_round1')->nullable();
            $table->string('rapper1_round2')->nullable();
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
        Schema::drop('open_battles');
    }
}
