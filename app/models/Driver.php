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

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function bookings()
    {
        return $this->hasMany('Booking');
    }
    
 

}
