<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_user', function(Blueprint $table)
		{
			$table->integer('permission_id')->unsigned()->index('permission_user_permission_id_foreign');
			$table->integer('user_id')->unsigned();
			$table->string('user_type');
			$table->primary(['user_id','permission_id','user_type']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('permission_user');
	}

}
