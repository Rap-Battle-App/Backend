<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->string('picture')->nullable();
            $table->string('city')->nullable();
            $table->text('about_me')->nullable();
            $table->boolean('rapper')->default(false);
            $table->boolean('notifications')->default(true);
            $table->integer('wins')->unsigned()->default(0);
            $table->integer('defeats')->unsigned()->default(0);
            $table->integer('rating');
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
