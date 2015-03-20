<?php
    
    class Offer extends Eloquent  {
    
        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'offers';
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
        protected $guarded = array('id','company_id');
        protected $fillable = array();
    
    
    
         public function Cars()
         {
             return $this->hasMany('OfferCar');
    
         }
         public function Amenities()
         {
    
             return $this->hasMany('Amenitie');
    
         }
    
         public function Timesheet()
         {
    
             return $this->hasMany('OfferTimes');
    
         }
    
         public function Drivers()
         {
    
             return $this->hasMany('OfferDriver');
    
         }
    
        public function set_active($active)
        {
            $this->set_attribute('active', $active?1:0);
        }
    
        public function get_active()
        {
            return $this->get_attribute('active')?true:false;
        }
    
    
         public function CheckCar($id)
         {
             $found = FALSE;
             foreach($this->Cars as  $car)
             {
                    if($car->car_id == $id)
                    {
                        $found =TRUE;
                        return $found;
                    }   
             }
             return $found;
         }
    
    
         public function CheckDriver($id)
         {
             $found = FALSE;
             foreach($this->Drivers as $driver)
             {
                    if($driver->driver_id == $id)
                    {
                        $found =TRUE;
                        return $found;
                    }   
             }
             return $found;
         }
    
         public function get_trip_type()
         {
             switch($this->calc_method)
             {
                case 'TAB':
                   return 'Traject depuis '.$this->departing_address.' '.$this->arrival_address;
                case 'TZG':
                    return 'Traject en Zone Geographique '.$this->departing_address;
                default:
                    return 'Traject Longue Distance';

             }
    


         }
    
    
         public function get_calc_method()
         {
             switch($this->calc_method)
             {
                case 1:
                    return 'KMS';
                case 2:
                    return 'MINUTE';
                default:
                    return 'FIXE';

             }
         }

         
         public function get_cost()
         {
            
              switch($this->calc_method)
             {
                case 1:
                    return 'PRIX PAR KM '.$this->cost;
                case 2:
                    return 'PRIX PAR MINUTE '.$this->cost;
                default:
                    return 'PRIX FIXE '.$this->cost;

             }
    
         }
    
    }
?>