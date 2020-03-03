<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToProductAttributesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('product_attributes', function(Blueprint $table)
		{
			$table->foreign('product_id')->references('id')->on('products')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('product_attributes', function(Blueprint $table)
		{
			$table->dropForeign('product_attributes_product_id_foreign');
		});
	}

}
