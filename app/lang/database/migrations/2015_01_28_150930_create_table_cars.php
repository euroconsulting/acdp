<?php
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    class CreateTableCars extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            //
                Schema::create('cars', function($table){
                 $table->increments('id');
                 $table->integer('car_id');
                 $table->string('brand');
                 $table->string('model');
                 $table->string('number');
                 $table->string('kms');
                 $table->decimal('km_unit');
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
