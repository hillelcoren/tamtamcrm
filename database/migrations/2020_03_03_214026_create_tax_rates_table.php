<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaxRatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tax_rates', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('account_id')->unsigned()->index();
			$table->integer('user_id')->unsigned()->nullable()->index('tax_rates_user_id_foreign');
			$table->timestamps();
			$table->softDeletes();
			$table->string('name', 100);
			$table->decimal('rate', 13, 3)->default(0.000);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tax_rates');
	}

}
