<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTasksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tasks', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title');
			$table->string('content');
			$table->string('task_color');
			$table->dateTime('due_date');
			$table->boolean('is_completed')->default(0);
			$table->timestamps();
			$table->integer('is_active')->default(1);
			$table->integer('task_status');
			$table->integer('created_by');
			$table->integer('task_type')->default(1);
			$table->integer('rating')->unsigned()->nullable();
			$table->integer('customer_id')->unsigned()->index('tasks_customer_id_foreign');
			$table->decimal('valued_at')->nullable();
			$table->integer('parent_id')->default(0);
			$table->integer('source_type')->unsigned()->nullable()->default(1)->index('tasks_source_type_foreign');
			$table->dateTime('start_date')->nullable();
			$table->string('time_log');
			$table->boolean('is_running')->default(0);
			$table->softDeletes();
			$table->integer('assigned_user_id')->unsigned()->nullable();
			$table->integer('account_id')->unsigned()->index();
			$table->string('custom_value1')->nullable();
			$table->string('custom_value2')->nullable();
			$table->integer('company_id')->nullable();
			$table->smallInteger('task_status_sort_order')->nullable();
			$table->dateTime('start_time')->nullable();
			$table->integer('duration')->nullable();
			$table->boolean('is_deleted')->default(0);
			$table->text('custom_value3', 65535)->nullable();
			$table->text('custom_value4', 65535)->nullable();
			$table->integer('project_id')->unsigned()->nullable();
			$table->integer('invoice_id')->unsigned()->nullable()->index('invoice_id');
			$table->integer('user_id')->unsigned()->default(9874)->index('user_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tasks');
	}

}
