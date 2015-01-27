<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerBookingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bookings', function($table){
             $table->increments('id');
             $table->integer('customer_id');
             $table->timestamp('departing_date');
             $table->string('departing_point');
             $table->string('arrival_point');
             $table->string('departing_address');
             $table->string('arrival_address');
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
