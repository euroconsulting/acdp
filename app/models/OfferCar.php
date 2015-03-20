<?php

class OfferCar extends Eloquent  {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'offers_cars';
    
    protected $guarded = array('id');
    protected $fillable = array();
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
 
      public function Offer()
      {
            return $this->belongsTo('OfferCar');
      }

      public function Car()
      {
            return $this->belongsTo('Car');
      }


     
}
?>