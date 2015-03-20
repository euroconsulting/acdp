<?php
    
    class Customer extends Eloquent  {
    
        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'customers';
    
        protected $guarded = array('id','company_id');
        protected $fillable = array();
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
        public function User()
        {
            return $this->belongsTo('User');
        }
    
        public function Company()
        {
            return $this->belongsTo('Company');
        }
    
        public function Bookings()
        {
            return $this->hasMany('Booking');
        }
    
    
    
        public function check_agenda($booking, $ex)
        {
            $dateToday = date('Y-m-d H:i:s');
    
            $customer_id = $this->id;
            $bookings = Booking::where(function($query) use ($booking, $customer_id)
                                {   
                                    $query->where('customer_id','=', $customer_id);
                                    $query->where('departing_date', '<=', $booking->departing_time);
                                    $query->where('arrival_date', '>', $booking->departing_time);
    
                                })
                                ->orwhere(function($query) use ($booking, $customer_id)
                                {
                                    $query->where('customer_id','=', $customer_id);
                                    $query->where('departing_date', '>=', $booking->departing_time);
                                    $query->where('departing_date', '<=', $booking->arrival_time);
    
                                })->get();
    
        
           if(count($bookings) > 0)
           {
            //Booking AGenda expcetion starts at 200
            //210 already booked on the current time
            //250 Unkonw exceptions
                if($ex)
                {
                   $booking->status= FALSE;
                   $booking->exception = 200;
                   $booking->exception_msg = "YOU ALREADY GOT A BOOKING AT THIS TIME IN YOU AGENDA";
                   return $booking;

                }
                else
                    return FALSE;
           
           }
           else    
           {

                if($ex)
                {

                    $booking->status= TRUE;
                    return $booking;
                }
                else
                    return TRUE;

               }
        }
    
    
    }
?>
