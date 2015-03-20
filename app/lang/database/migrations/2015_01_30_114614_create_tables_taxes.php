<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablesTaxes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		 Schema::create('taxes', function($table){
                     $table->integer('id');
                     $table->string('location');
                     $table->string('radius');
                     $table->decimal('minute_cost');
                     $table->decimal('kms_cost');
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
