<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventTaskTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('event_task', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('task_id')->unsigned()->index('task_id');
			$table->integer('event_id')->unsigned()->index('event_id');
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
		Schema::drop('event_task');
	}

}
