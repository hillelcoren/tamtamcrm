<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateExpensesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('expenses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('account_id')->unsigned()->index();
			$table->integer('company_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->index('expenses_user_id_foreign');
			$table->integer('invoice_id')->unsigned()->nullable();
			$table->integer('customer_id')->unsigned()->nullable();
			$table->integer('bank_id')->unsigned()->nullable();
			$table->integer('invoice_currency_id')->unsigned();
			$table->integer('expense_currency_id')->unsigned();
			$table->integer('invoice_category_id')->unsigned()->nullable();
			$table->integer('payment_type_id')->unsigned()->nullable();
			$table->integer('recurring_expense_id')->unsigned()->nullable();
			$table->boolean('is_deleted')->default(0);
			$table->decimal('amount', 13);
			$table->decimal('foreign_amount', 13);
			$table->decimal('exchange_rate', 13, 4);
			$table->string('tax_name1')->nullable();
			$table->decimal('tax_rate1', 13, 3)->default(0.000);
			$table->string('tax_name2')->nullable();
			$table->decimal('tax_rate2', 13, 3)->default(0.000);
			$table->string('tax_name3')->nullable();
			$table->decimal('tax_rate3', 13, 3)->default(0.000);
			$table->date('expense_date')->nullable();
			$table->date('payment_date')->nullable();
			$table->text('public_notes', 65535)->nullable();
			$table->text('transaction_reference', 65535);
			$table->boolean('should_be_invoiced')->default(0);
			$table->boolean('invoice_documents')->nullable()->default(1);
			$table->string('transaction_id')->nullable();
			$table->string('custom_value1')->nullable();
			$table->string('custom_value2')->nullable();
			$table->string('custom_value3')->nullable();
			$table->string('custom_value4')->nullable();
			$table->integer('status_id')->default(1);
			$table->text('private_notes', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('expenses');
	}

}
