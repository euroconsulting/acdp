<?php

class DriverCars extends Eloquent  {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'drivers_cars';
  
    protected $fillable = [];
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */

    public function Cars()
    {
        return $this->belongsTo('Car');
    }

    public function Drivers()
    {
        return $this->belongsTo('Driver');
    }
    

}
