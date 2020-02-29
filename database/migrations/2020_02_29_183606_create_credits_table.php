<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCreditsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('credits', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('customer_id')->unsigned()->index();
			$table->integer('user_id')->unsigned()->index('credits_user_id_foreign');
			$table->integer('assigned_user_id')->unsigned()->nullable();
			$table->integer('account_id')->unsigned()->index();
			$table->integer('status_id')->unsigned();
			$table->string('number')->nullable();
			$table->date('date')->nullable();
			$table->boolean('is_deleted')->default(0);
			$table->decimal('total', 16, 4);
			$table->decimal('balance', 16, 4);
			$table->dateTime('last_viewed')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->integer('invoice_id')->unsigned()->nullable();
			$table->text('footer', 65535)->nullable();
			$table->text('public_notes', 65535)->nullable();
			$table->text('terms', 65535)->nullable();
			$table->string('custom_value1')->nullable();
			$table->string('custom_value2')->nullable();
			$table->string('custom_value3')->nullable();
			$table->string('custom_value4')->nullable();
			$table->text('line_items', 16777215)->nullable();
			$table->dateTime('due_date')->nullable();
			$table->dateTime('partial_due_date')->nullable();
			$table->boolean('is_amount_discount')->default(0);
			$table->string('po_number')->nullable();
			$table->text('backup', 16777215)->nullable();
			$table->decimal('discount_total', 16, 4)->default(0.0000);
			$table->decimal('tax_total', 16, 4)->default(0.0000);
			$table->text('private_notes', 65535)->nullable();
			$table->string('tax_rate_name')->nullable();
			$table->decimal('tax_rate', 13, 3)->default(0.000);
			$table->unique(['account_id','number']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('credits');
	}

}
