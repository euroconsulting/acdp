<?php
    
    
    use Illuminate\Auth\UserTrait;
    use Illuminate\Auth\UserInterface;
    use Illuminate\Auth\Reminders\RemindableTrait;
    use Illuminate\Auth\Reminders\RemindableInterface;
    
    
    class User extends Eloquent implements UserInterface, RemindableInterface {
        use UserTrait, RemindableTrait;
        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'users';
    
      
        protected $guarded = array('id', 'password');
        protected $fillable = array();
    
    
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
        protected $appends = array('Fullname');
    


        public function getFullnameAttribute()
        {
            return $this->first_name.' '.$this->last_name; 
        }
    
        protected $hidden = array('password', 'remember_token');
    
        public function Driver()
        {
            return $this->hasOne('Driver');
        }
    
        public function Customer()
        {
            return $this->hasOne('Customer');
        }
    
    
        public function Company()
        {
            return $this->belongsTo('Company');
        }
    
    


        public function Times()
        {
            return $this->hasMany('UserTimes');
        }
    
        public function __construct()
        {
    
        }
    
        public function get_trip_type(){
    
    
        }
    
        public function get_calc_method(){
    
          

    
        }
    
        public function get_cost(){
    
    
        }
    
        public function get_age()
        {
            if($this->date_birth != NULL)
            {
                $today = new date();
            }
            else
                return 'Not Defined yet';
        }
    
        public function rules()
        {
            $rules =[];
            if(count(Auth::user()) >  0 && Auth::user()->active == 1)
            {
                if(count(Auth::user()->Driver)  > 0 && Auth::user()->Driver->active == 1)
                    array_push($rules, 'driver');
    
                if(count(Auth::user()->Customer) > 0 && Auth::user()->Customer->active == 1)
                    array_push($rules, 'customer');
    
                if(Auth::user()->admin == 1 && Auth::user()->Company->type == 'client')
                    array_push($rules, 'admin');
    
                if(Auth::user()->admin == 1 && Auth::user()->Company->type == 'master')
                    array_push($rules, 'super-admin');
            }
    
            return $rules;
    
        }
    
    
        public function role()
        {
            $role ="user";
            if(count(Auth::user()) >  0 && Auth::user()->active == 1)
            {
                if(Auth::user()->admin == 1 && Auth::user()->Company->type == 'master')
                    $role ="super-admin";
    
                if(Auth::user()->admin == 1 && Auth::user()->Company->type == 'client')
                    $role ="admin";
            }
    
            return $role;
    
        }
    
    
        public function is_super_admin()
        {
              if(Auth::user()->admin == 1 && Auth::user()->Company->type == 'master')
                return TRUE;
              else
                return FALSE;
    
        }

        
        public function is_admin()
        {
              if(Auth::user()->admin == 1)
                return TRUE;
              else
                return FALSE;
    
        }
    
    
        public function is_customer()
        {
              if(count($this->Customer) && $this->Customer->active == 1)
                return TRUE;
              else
                return FALSE;
    
        }
    
    
    
        public function is_driver()
        {
              if(count($this->Driver) && $this->Driver->active == 1)
                return TRUE;
              else
                return FALSE;
    
        }
    

        public function get_country()
        {
            return "your_country";
    
        }
    
        public function get_formated_address()
        {
            return $this->address.', nº '.$this->address_number.' '.$this->address_code_postal.' - '.$this->country_id;
    
        }


        public function get_document_contact_details()
        {
                $formated_details =  $this->address.', nº '.$this->address_number.' </br>'.$this->address_code_postal.' - '.$this->country_id;
                if(strlen($this->phone1) > 0)
                {
                    $formated_details.=' </br> P: '.$this->phone1;

                }
    
                return $formated_details;
        }

    }
