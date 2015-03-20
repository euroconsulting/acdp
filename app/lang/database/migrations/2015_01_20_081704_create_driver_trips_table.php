<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriverTripsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
			Schema::create('driver_trips', function($table){
             $table->increments('id');
             $table->integer('driver_id');
             $table->integer('booking_id');
             $table->timestamp('start');
             $table->timestamp('end');
             $table->string('start_pos');
             $table->string('end_position');
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
