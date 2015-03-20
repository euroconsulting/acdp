<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTimeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		 Schema::create('offers_times', function($table){
                     $table->integer('id');
                     $table->integer('offer_id');
                     $table->string('day_week');
                     $table->timestamp('start');
                     $table->timestamp('end');
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
