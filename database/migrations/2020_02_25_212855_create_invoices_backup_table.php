<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvoicesBackupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invoices_backup', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('customer_id');
			$table->integer('payment_type');
			$table->decimal('total');
			$table->timestamps();
			$table->integer('invoice_status')->default(1);
			$table->dateTime('due_date');
			$table->integer('finance_type');
			$table->decimal('sub_total');
			$table->decimal('tax_total');
			$table->decimal('discount_total');
			$table->integer('parent_id')->nullable();
			$table->softDeletes();
			$table->integer('is_recurring')->default(0);
			$table->date('invoice_date');
			$table->date('start_date');
			$table->date('end_date');
			$table->date('recurring_due_date');
			$table->integer('frequency');
			$table->string('notes')->nullable();
			$table->decimal('partial');
			$table->decimal('balance');
			$table->date('partial_due_date')->nullable();
			$table->string('terms')->nullable();
			$table->string('footer')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('invoices_backup');
	}

}
