<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompanyLedgersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_ledgers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('account_id')->unsigned()->index('company_ledgers_account_id_foreign');
			$table->integer('customer_id')->unsigned()->nullable()->index('company_ledgers_customer_id_foreign');
			$table->integer('user_id')->unsigned()->nullable();
			$table->decimal('adjustment', 16, 4)->nullable();
			$table->decimal('balance', 16, 4)->nullable();
			$table->text('notes', 65535)->nullable();
			$table->text('hash', 65535)->nullable();
			$table->integer('company_ledgerable_id')->unsigned();
			$table->string('company_ledgerable_type');
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
		Schema::drop('company_ledgers');
	}

}
