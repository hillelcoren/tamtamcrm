<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('countries', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name')->unique();
			$table->string('iso')->unique();
			$table->string('iso3')->nullable();
			$table->integer('numcode')->nullable();
			$table->integer('phonecode');
			$table->integer('status');
			$table->timestamps();
			$table->boolean('swap_postal_code')->default(0);
			$table->boolean('swap_currency_symbol')->default(0);
			$table->string('thousand_separator', 100)->nullable();
			$table->string('decimal_separator', 100)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('countries');
	}

}
