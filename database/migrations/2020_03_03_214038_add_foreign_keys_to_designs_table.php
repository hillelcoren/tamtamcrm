<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToDesignsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('designs', function(Blueprint $table)
		{
			$table->foreign('account_id')->references('id')->on('accounts')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('designs', function(Blueprint $table)
		{
			$table->dropForeign('designs_account_id_foreign');
		});
	}

}
