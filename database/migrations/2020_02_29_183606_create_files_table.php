<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('files', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index('files_user_id_foreign');
			$table->timestamps();
			$table->integer('is_active')->default(1);
			$table->string('file_path');
			$table->integer('account_id')->unsigned()->index();
			$table->string('preview')->nullable();
			$table->string('name')->nullable();
			$table->string('type')->nullable();
			$table->string('disk')->nullable();
			$table->string('hash', 100)->nullable();
			$table->integer('size')->unsigned()->nullable();
			$table->integer('width')->unsigned()->nullable();
			$table->integer('height')->unsigned()->nullable();
			$table->boolean('is_default')->default(0);
			$table->integer('documentable_id')->unsigned();
			$table->string('documentable_type');
			$table->integer('company_id')->unsigned()->nullable();
			$table->string('custom_value1')->nullable();
			$table->string('custom_value2')->nullable();
			$table->string('custom_value3')->nullable();
			$table->string('custom_value4')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('files');
	}

}
