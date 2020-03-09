<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompanyUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_user', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('company_id');
			$table->integer('user_id');
			$table->timestamps();
			$table->integer('is_admin');
			$table->integer('is_owner');
			$table->integer('is_locked');
			$table->text('permissions', 65535);
			$table->text('settings', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('company_user');
	}

}
