<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToQuoteInvitationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('quote_invitations', function(Blueprint $table)
		{
			$table->foreign('account_id')->references('id')->on('accounts')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('client_contact_id', 'quote_invitations_customer_id_foreign')->references('id')->on('client_contacts')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('quote_id')->references('id')->on('quotes')->onUpdate('RESTRICT')->onDelete('CASCADE');
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
		Schema::table('quote_invitations', function(Blueprint $table)
		{
			$table->dropForeign('quote_invitations_account_id_foreign');
			$table->dropForeign('quote_invitations_customer_id_foreign');
			$table->dropForeign('quote_invitations_quote_id_foreign');
			$table->dropForeign('quote_invitations_user_id_foreign');
		});
	}

}
