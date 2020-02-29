<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGatewaysTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('gateways', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('key')->unique();
			$table->string('provider');
			$table->boolean('visible')->default(1);
			$table->integer('sort_order')->unsigned()->default(10000);
			$table->string('site_url', 200)->nullable();
			$table->boolean('is_offsite')->default(0);
			$table->boolean('is_secure')->default(0);
			$table->text('fields', 16777215)->nullable();
			$table->integer('default_gateway_type_id')->unsigned()->default(1);
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('gateways');
	}

}
