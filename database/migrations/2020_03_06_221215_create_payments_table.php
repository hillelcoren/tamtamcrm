<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('account_id')->unsigned()->index('payments_account_id_foreign');
			$table->integer('customer_id')->unsigned()->index('payments_customer_id_foreign');
			$table->integer('user_id')->unsigned()->nullable()->index('payments_user_id_foreign');
			$table->integer('assigned_user_id')->unsigned()->nullable();
			$table->integer('invitation_id')->unsigned()->nullable();
			$table->integer('company_gateway_id')->unsigned()->nullable()->index('payments_company_gateway_id_foreign');
			$table->integer('type_id')->unsigned()->nullable()->index('payments_payment_type_id_foreign');
			$table->integer('status_id')->unsigned();
			$table->decimal('amount', 16, 4)->default(0.0000);
			$table->decimal('refunded', 16, 4)->default(0.0000);
			$table->date('date')->nullable();
			$table->string('transaction_reference')->nullable();
			$table->string('payer_id')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->boolean('is_deleted')->default(0);
			$table->boolean('is_manual')->default(0);
			$table->integer('company_id')->unsigned()->nullable();
			$table->string('number')->nullable();
			$table->decimal('applied', 16, 4)->default(0.0000);
			$table->string('custom_value1')->nullable();
			$table->string('custom_value2')->nullable();
			$table->string('custom_value3')->nullable();
			$table->string('custom_value4')->nullable();
			$table->text('private_notes', 65535)->nullable();
			$table->integer('client_contact_id')->unsigned()->nullable()->index('payments_client_contact_id_foreign');
			$table->decimal('exchange_rate', 16, 6)->default(1.000000);
			$table->integer('currency_id')->unsigned();
			$table->integer('exchange_currency_id')->unsigned();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payments');
	}

}
