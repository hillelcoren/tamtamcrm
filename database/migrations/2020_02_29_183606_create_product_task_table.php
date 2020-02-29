<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductTaskTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_task', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('product_id')->unsigned()->index('product_task_product_id_foreign');
			$table->integer('task_id')->unsigned()->index('product_task_task_id_foreign');
			$table->timestamps();
			$table->string('name')->nullable();
			$table->string('sku')->nullable();
			$table->integer('user_id');
			$table->integer('account_id')->unsigned()->index('account_id');
			$table->text('attributes', 65535)->nullable();
			$table->integer('quantity');
			$table->integer('status')->unsigned()->default(1)->index('status');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('product_task');
	}

}
