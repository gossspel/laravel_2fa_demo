<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('users', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('first_name', 20);
            $table->string('last_name', 20);
            $table->string('email', 100)->unique();
            $table->string('password', 64);
            $table->string('two_factor_mode', 4);
            $table->string('two_factor_secret', 50);
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
