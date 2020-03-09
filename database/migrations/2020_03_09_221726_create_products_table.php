<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('products', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('company_id')->unsigned()->nullable()->index('products_brand_id_index');
			$table->string('sku');
			$table->string('name');
			$table->string('slug');
			$table->text('description', 65535)->nullable();
			$table->decimal('price');
			$table->integer('status')->default(1);
			$table->timestamps();
			$table->string('cover')->nullable();
			$table->decimal('quantity', 16, 4)->default(0.0000);
			$table->decimal('cost', 16, 4)->default(0.0000);
			$table->softDeletes();
			$table->integer('account_id')->unsigned()->index('products_account_id_foreign');
			$table->integer('user_id')->unsigned();
			$table->integer('assigned_user_id')->unsigned()->nullable();
			$table->text('notes', 65535)->nullable();
			$table->boolean('is_deleted')->default(0);
			$table->string('custom_value1')->nullable();
			$table->string('custom_value2')->nullable();
			$table->string('custom_value3')->nullable();
			$table->string('custom_value4')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('products');
	}

}
