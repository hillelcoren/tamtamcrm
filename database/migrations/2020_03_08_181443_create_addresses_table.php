<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddressesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('addresses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('alias');
			$table->string('address_1');
			$table->string('address_2')->nullable();
			$table->string('zip')->nullable();
			$table->string('state_code')->nullable();
			$table->string('city')->nullable();
			$table->integer('province_id')->nullable();
			$table->integer('country_id')->unsigned()->default(225)->index();
			$table->integer('customer_id')->unsigned()->index();
			$table->integer('status')->default(1);
			$table->timestamps();
			$table->softDeletes();
			$table->integer('address_type')->default(1);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('addresses');
	}

}
