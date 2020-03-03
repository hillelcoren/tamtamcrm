<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClientContactsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('client_contacts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('account_id')->unsigned()->index();
			$table->integer('customer_id')->unsigned()->index();
			$table->integer('user_id')->unsigned()->index();
			$table->string('first_name')->nullable();
			$table->string('last_name')->nullable();
			$table->string('phone')->nullable();
			$table->string('custom_value1')->nullable();
			$table->string('custom_value2')->nullable();
			$table->string('custom_value3')->nullable();
			$table->string('custom_value4')->nullable();
			$table->string('email', 100)->nullable();
			$table->dateTime('email_verified_at')->nullable();
			$table->string('confirmation_code')->nullable();
			$table->boolean('is_primary')->default(0);
			$table->boolean('confirmed')->default(0);
			$table->dateTime('last_login')->nullable();
			$table->smallInteger('failed_logins')->nullable();
			$table->string('accepted_terms_version')->nullable();
			$table->string('avatar')->nullable();
			$table->string('avatar_type')->nullable();
			$table->string('avatar_size')->nullable();
			$table->string('password');
			$table->boolean('is_locked')->default(0);
			$table->boolean('send_email')->default(1);
			$table->string('contact_key')->nullable();
			$table->string('remember_token', 100)->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('client_contacts');
	}

}
