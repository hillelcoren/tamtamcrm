<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCommentTaskTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('comment_task', function(Blueprint $table)
		{
			$table->foreign('comment_id', 'task_comment_comment_id_foreign')->references('id')->on('comments')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('task_id', 'task_comment_task_id_foreign')->references('id')->on('tasks')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('comment_task', function(Blueprint $table)
		{
			$table->dropForeign('task_comment_comment_id_foreign');
			$table->dropForeign('task_comment_task_id_foreign');
		});
	}

}
