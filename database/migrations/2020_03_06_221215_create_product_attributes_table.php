<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductAttributesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('product_attributes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('range_from');
			$table->decimal('range_to');
			$table->decimal('interest_rate')->nullable();
			$table->integer('product_id')->unsigned()->index('product_attributes_product_id_foreign');
			$table->timestamps();
			$table->float('minimum_downpayment')->nullable()->default(0.00);
			$table->float('payable_months')->default(12.00);
			$table->integer('number_of_years')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('product_attributes');
	}

}
