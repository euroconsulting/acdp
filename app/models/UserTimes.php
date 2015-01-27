<?php
    class UserTimes extends Eloquent  {
    
        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'users_times';
        protected $fillable = ['id', 'user_id','day_week', 'start','end'];
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
