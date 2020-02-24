<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommentTaskTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comment_task', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('task_id')->unsigned()->index('task_comment_task_id_foreign');
			$table->integer('comment_id')->unsigned()->index('task_comment_comment_id_foreign');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('comment_task');
	}

}
