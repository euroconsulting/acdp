<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		  Schema::create('offers', function($table){
                     $table->integer('id');
                     $table->string('title');
                     $table->string('description');
                     $table->string('departing');
                     $table->string('arrival');
                     $table->boolean('active');
                     $table->string('start_date');
                     $table->string('end_date');
                     $table->string('process_type');
                     $table->decimal('value');
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
