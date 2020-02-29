<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClientGatewayTokensTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('client_gateway_tokens', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('account_id')->unsigned()->index('client_gateway_tokens_account_id_foreign');
			$table->integer('customer_id')->unsigned()->nullable()->index('client_gateway_tokens_customer_id_foreign');
			$table->text('token', 65535)->nullable();
			$table->integer('company_gateway_id')->unsigned()->default(1);
			$table->string('gateway_customer_reference')->nullable();
			$table->integer('gateway_type_id')->unsigned()->default(1);
			$table->boolean('is_default')->default(0);
			$table->text('meta', 65535)->nullable();
			$table->softDeletes();
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
		Schema::drop('client_gateway_tokens');
	}

}
