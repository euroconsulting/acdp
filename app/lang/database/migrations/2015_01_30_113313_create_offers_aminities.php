<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersAminities extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('offers_amenities', function($table){
                     $table->integer('offer_id');
                     $table->integer('amenitie_id');
                     $table->string('description');
                     $table->decimal('cost');
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
