<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customers', function($table){
             $table->increments('id');
             $table->integer('user_id');
             $table->integer('company_id');
             $table->integer('app_id');
             $table->integer('front_id');
             $table->string('mangopay_user_id');
             $table->string('avatar_url');
             $table->timestamp('last_connexion');
             $table->timestamps();
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
