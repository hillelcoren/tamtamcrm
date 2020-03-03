<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDepartmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('departments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('department_manager')->unsigned()->index('departments_department_manager_foreign');
			$table->string('name')->unique('name');
			$table->timestamps();
			$table->integer('_lft')->unsigned()->default(0);
			$table->integer('_rgt')->unsigned()->default(0);
			$table->integer('parent_id')->unsigned()->nullable();
			$table->index(['_lft','_rgt','parent_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('departments');
	}

}
