<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPermissionUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('permission_user', function(Blueprint $table)
		{
			$table->foreign('user_id', 'permission_user_ibfk_1')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('permission_id')->references('id')->on('permissions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('permission_user', function(Blueprint $table)
		{
			$table->dropForeign('permission_user_ibfk_1');
			$table->dropForeign('permission_user_permission_id_foreign');
		});
	}

}
