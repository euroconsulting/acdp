<?php
    
    class Car extends Eloquent  {
    
        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'cars';
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
        protected $guarded = ['id', 'company_id'];
        protected $fillable =[];
    
         protected $appends = array('fullname');
    
         public function getfullnameAttribute()
         {
                return $this->brand.' '.$this->model; 
         }
    
    
         public function Offers()
         {
             return $this->hasMany('OfferCar');
         }
    
         public function Drivers()
         {
             return $this->hasMany('Drivers');
         }

    
         public function Company()
         {
             return $this->belongsTo('Company');
         }
  
    
    
    
    }
?>