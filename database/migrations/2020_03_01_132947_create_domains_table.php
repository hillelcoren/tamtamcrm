<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDomainsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('domains', function(Blueprint $table)
		{
			$table->increments('id');
			$table->enum('plan', array('pro','enterprise','white_label'))->nullable();
			$table->enum('plan_term', array('month','year'))->nullable();
			$table->date('plan_started')->nullable();
			$table->date('plan_paid')->nullable();
			$table->date('plan_expires')->nullable();
			$table->string('user_agent')->nullable();
			$table->integer('payment_id')->unsigned()->nullable()->index();
			$table->integer('default_account_id')->unsigned();
			$table->date('trial_started')->nullable();
			$table->enum('trial_plan', array('pro','enterprise'))->nullable();
			$table->enum('pending_plan', array('pro','enterprise','free'))->nullable();
			$table->enum('pending_term', array('month','year'))->nullable();
			$table->decimal('plan_price', 7)->nullable();
			$table->decimal('pending_plan_price', 7)->nullable();
			$table->smallInteger('num_users')->default(1);
			$table->smallInteger('pending_num_users')->default(1);
			$table->string('utm_source')->nullable();
			$table->string('utm_medium')->nullable();
			$table->string('utm_campaign')->nullable();
			$table->string('utm_term')->nullable();
			$table->string('utm_content')->nullable();
			$table->float('discount')->default(0.00);
			$table->date('discount_expires')->nullable();
			$table->enum('bluevine_status', array('ignored','signed_up'))->nullable();
			$table->string('referral_code')->nullable();
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
		Schema::drop('domains');
	}

}
