<?php

class OfferAmenitie extends Eloquent  {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'offers_amenities';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
 
      public function offer()
      {
            return $this->belongsTo('Offer');
      }
}
?>