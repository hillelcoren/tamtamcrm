<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('events', function(Blueprint $table)
		{
			$table->increments('id');
			$table->dateTime('beginDate');
			$table->dateTime('endDate');
			$table->integer('customer_id')->unsigned()->index('customer_id');
			$table->string('location', 100);
			$table->integer('created_by')->unsigned()->index('created_by');
			$table->string('title', 100);
			$table->timestamps();
			$table->integer('event_type')->unsigned()->default(1)->index('event_type');
			$table->string('description')->nullable();
			$table->softDeletes();
			$table->integer('account_id')->unsigned()->index('account_id');
			$table->boolean('is_deleted')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('events');
	}

}
