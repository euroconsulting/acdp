<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

        Schema::create('companies', function($table){
            $table->increments('id');
            $table->string('name');
            $table->string('address');
            $table->string('mangopay_user_id');
            $table->string('domain');
            $table->string('phone');
            $table->string('trade_register_number');
            $table->string('driver_licence_number');
            $table->timestamps();
        });


            //$table->increments('company_id');
            //$table->string('start_point');
            //$table->string('end_point');
            //$table->timestamp('start');
            //$table->timestamp('end');
            //$table->integer('user_id');
            //$table->integer('car_id');
            //$table->timestamps();
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
