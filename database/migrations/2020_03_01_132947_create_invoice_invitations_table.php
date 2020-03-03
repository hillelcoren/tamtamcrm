<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvoiceInvitationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invoice_invitations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('account_id')->unsigned()->index('invoice_invitations_account_id_foreign');
			$table->integer('user_id')->unsigned()->index('invoice_invitations_user_id_foreign');
			$table->integer('client_contact_id')->unsigned();
			$table->integer('invoice_id')->unsigned()->index();
			$table->string('key')->index();
			$table->string('transaction_reference')->nullable();
			$table->string('message_id')->nullable();
			$table->text('email_error', 16777215)->nullable();
			$table->text('signature_base64', 65535)->nullable();
			$table->dateTime('signature_date')->nullable();
			$table->dateTime('sent_date')->nullable();
			$table->dateTime('viewed_date')->nullable();
			$table->dateTime('opened_date')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->index(['deleted_at','invoice_id']);
			$table->unique(['client_contact_id','invoice_id'], 'invoice_invitations_customer_id_invoice_id_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('invoice_invitations');
	}

}
