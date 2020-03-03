<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('payments', function(Blueprint $table)
		{
			$table->foreign('account_id')->references('id')->on('accounts')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('customer_id')->references('id')->on('customers')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('type_id', 'payments_payment_type_id_foreign')->references('id')->on('payment_methods')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('payments', function(Blueprint $table)
		{
			$table->dropForeign('payments_account_id_foreign');
			$table->dropForeign('payments_customer_id_foreign');
			$table->dropForeign('payments_payment_type_id_foreign');
			$table->dropForeign('payments_user_id_foreign');
		});
	}

}
