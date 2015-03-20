<?php
    
    
    class OfferTimes extends Eloquent  {
    
        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'offers_times';
        protected $fillable = ['id', 'offer_id','day_week', 'start','end'];
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
        public function Offer()
        {
            return $this->belongsTo('Offer');
        }
    
    }
    
    
