<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToProductTaskTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('product_task', function(Blueprint $table)
		{
			$table->foreign('account_id', 'product_task_ibfk_1')->references('id')->on('accounts')->onUpdate('NO ACTION')->onDelete('CASCADE');
			$table->foreign('status', 'product_task_ibfk_2')->references('id')->on('order_status')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('product_id')->references('id')->on('products')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('task_id')->references('id')->on('tasks')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('product_task', function(Blueprint $table)
		{
			$table->dropForeign('product_task_ibfk_1');
			$table->dropForeign('product_task_ibfk_2');
			$table->dropForeign('product_task_product_id_foreign');
			$table->dropForeign('product_task_task_id_foreign');
		});
	}

}
