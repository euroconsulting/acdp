<?php

class OfferDriver extends Eloquent  {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'offers_drivers';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
    
      protected $fillable = array('offer_id','driver_id');


      public function Offer()
      {
            return $this->belongsTo('Offer');
      }

      public function Driver()
      {
            return $this->belongsTo('Driver');
      }


}
?>