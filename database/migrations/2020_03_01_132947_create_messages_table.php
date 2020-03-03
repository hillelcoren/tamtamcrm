<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('messages', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('user_id')->unsigned()->index('messages_user_id_foreign');
			$table->integer('customer_id')->unsigned()->index('customer_id');
			$table->string('message');
			$table->integer('has_seen')->default(1);
			$table->timestamps();
			$table->integer('direction')->default(1);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('messages');
	}

}
