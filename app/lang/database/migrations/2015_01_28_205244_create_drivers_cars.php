<?php
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    class CreateDriversCars extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
             Schema::create('drivers_cars', function($table){
                     $table->integer('car_id');
                     $table->integer('driver_id');
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
