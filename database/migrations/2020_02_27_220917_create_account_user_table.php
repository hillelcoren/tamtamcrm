<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccountUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('account_user', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('account_id')->unsigned()->index();
			$table->integer('user_id')->unsigned()->index();
			$table->text('permissions', 16777215)->nullable();
			$table->text('settings', 16777215)->nullable();
			$table->boolean('is_owner')->default(0);
			$table->boolean('is_admin')->default(0);
			$table->boolean('is_locked')->default(0);
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
		Schema::drop('account_user');
	}

}
