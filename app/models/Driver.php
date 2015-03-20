<?php

class Driver extends Eloquent  {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'drivers';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */

     protected $guarded = array('id','company_id');
     protected $fillable = array();



    public function User()
    {
        return $this->belongsTo('User');
    }

    public function Bookings()
    {
        return $this->hasMany('Booking');
    }
    
    public function Cars()
    {
        return $this->hasMany('DriverCars')->select(array('car_id'));
    }
 
    public function CheckCar($id)
    {
        $found = FALSE;
        foreach($this->Cars as  $car)
        {
            if($car->car_id == $id)
            {
                $found =TRUE;
                return $found;
            }   
        }
        return $found;
     }
 
   
 

}
