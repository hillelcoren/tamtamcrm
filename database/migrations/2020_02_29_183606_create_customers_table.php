<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCustomersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('account_id')->unsigned()->index('customers_account_id_foreign');
			$table->integer('user_id')->unsigned();
			$table->integer('currency_id')->unsigned()->nullable();
			$table->integer('company_id')->unsigned()->nullable()->index('company_id');
			$table->integer('customer_type')->unsigned()->nullable();
			$table->integer('default_payment_method')->unsigned()->nullable();
			$table->integer('assigned_user_id')->unsigned()->nullable();
			$table->integer('status')->unsigned()->default(1);
			$table->string('first_name');
			$table->string('password');
			$table->string('remember_token');
			$table->string('last_name');
			$table->string('website')->nullable();
			$table->string('email')->nullable();
			$table->string('logo')->nullable();
			$table->string('job_title')->nullable();
			$table->string('phone')->nullable();
			$table->decimal('balance', 16, 4)->default(0.0000);
			$table->decimal('paid_to_date', 16, 4)->default(0.0000);
			$table->decimal('credit_balance', 16, 4)->default(0.0000);
			$table->dateTime('last_login')->nullable();
			$table->text('settings', 16777215)->nullable();
			$table->boolean('is_deleted')->default(0);
			$table->integer('group_settings_id')->unsigned()->nullable();
			$table->string('vat_number')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->boolean('send_invoice')->default(1);
			$table->string('custom_value1')->nullable();
			$table->string('custom_value2')->nullable();
			$table->string('custom_value3')->nullable();
			$table->string('custom_value4')->nullable();
			$table->string('id_number')->nullable();
			$table->text('public_notes', 65535)->nullable();
			$table->text('private_notes', 65535)->nullable();
			$table->integer('industry_id')->unsigned()->nullable()->index('industry_id');
			$table->integer('size_id')->unsigned()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('customers');
	}

}
