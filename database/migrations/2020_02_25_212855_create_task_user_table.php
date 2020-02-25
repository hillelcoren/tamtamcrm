<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaskUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('task_user', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('task_id')->unsigned()->index('task_user_task_id_foreign');
			$table->integer('user_id')->unsigned()->index('task_user_user_id_foreign');
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
		Schema::drop('task_user');
	}

}
