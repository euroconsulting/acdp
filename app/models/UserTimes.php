<?php
    class UserTimes extends Eloquent  {
    
        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $guarded = array('id');
        protected $fillable = [];

         protected $table = 'users_times';
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
        public function user()
        {
            return $this->belongsTo('User');
        }

        public function driver()
        {
            return $this->belongsTo('Driver');
        }
    
    
    }
