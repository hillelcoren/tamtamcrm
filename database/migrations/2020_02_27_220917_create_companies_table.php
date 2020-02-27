<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('companies', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name')->unique('name');
			$table->timestamps();
			$table->string('website');
			$table->string('phone_number');
			$table->string('email');
			$table->string('address_1');
			$table->string('address_2')->nullable();
			$table->string('town');
			$table->string('city');
			$table->string('postcode');
			$table->integer('country_id')->unsigned()->index('country_id');
			$table->integer('currency_id')->unsigned()->nullable()->index('currency_id');
			$table->integer('industry_id')->nullable();
			$table->text('settings', 16777215)->nullable();
			$table->integer('assigned_user_id')->unsigned()->nullable();
			$table->text('notes', 65535)->nullable();
			$table->integer('user_id')->unsigned()->index();
			$table->integer('account_id')->unsigned()->index();
			$table->string('custom_value1')->nullable();
			$table->string('custom_value2')->nullable();
			$table->string('custom_value3')->nullable();
			$table->string('custom_value4')->nullable();
			$table->softDeletes();
			$table->boolean('is_deleted')->default(0);
			$table->string('vat_number')->nullable();
			$table->string('transaction_name')->nullable();
			$table->string('id_number')->nullable();
			$table->decimal('balance', 16, 4)->nullable();
			$table->decimal('paid_to_date', 16, 4)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('companies');
	}

}
