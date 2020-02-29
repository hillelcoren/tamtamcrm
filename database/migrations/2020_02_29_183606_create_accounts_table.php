<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('accounts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('ip')->nullable();
			$table->string('logo')->nullable();
			$table->boolean('convert_products')->default(0);
			$table->boolean('fill_products')->default(1);
			$table->boolean('update_products')->default(1);
			$table->boolean('show_product_details')->default(1);
			$table->boolean('custom_surcharge_taxes1')->default(0);
			$table->boolean('custom_surcharge_taxes2')->default(0);
			$table->boolean('custom_surcharge_taxes3')->default(0);
			$table->boolean('custom_surcharge_taxes4')->default(0);
			$table->boolean('enable_invoice_quantity')->default(1);
			$table->boolean('show_product_cost')->default(0);
			$table->integer('enabled_tax_rates')->unsigned()->default(1);
			$table->boolean('enable_product_cost')->default(0);
			$table->boolean('enable_product_quantity')->default(1);
			$table->boolean('default_quantity')->default(1);
			$table->string('subdomain')->nullable();
			$table->string('db')->nullable();
			$table->string('first_day_of_week')->nullable();
			$table->string('first_month_of_year')->nullable();
			$table->string('portal_mode')->default('subdomain');
			$table->string('portal_domain')->nullable();
			$table->smallInteger('enable_modules')->default(0);
			$table->text('custom_fields', 16777215);
			$table->text('settings', 16777215);
			$table->timestamps();
			$table->softDeletes();
			$table->integer('domain_id')->unsigned()->default(1)->index();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('accounts');
	}

}
