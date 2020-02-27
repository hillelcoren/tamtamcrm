<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
			$table->string('first_name');
			$table->string('last_name');
			$table->string('profile_photo')->nullable();
			$table->string('username')->unique();
			$table->timestamps();
			$table->string('email')->default('1')->unique('email');
			$table->string('password');
			$table->string('auth_token')->nullable()->unique('users_api_token_unique');
			$table->integer('is_active')->default(1);
			$table->softDeletes();
			$table->string('gender')->nullable();
			$table->string('phone_number')->nullable();
			$table->date('dob')->nullable();
			$table->string('job_description')->nullable();
			$table->string('custom_value1')->nullable();
			$table->string('custom_value2')->nullable();
			$table->string('custom_value3')->nullable();
			$table->string('custom_value4')->nullable();
			$table->integer('is_deleted')->default(0);
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
