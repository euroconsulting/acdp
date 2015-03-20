<?php
    
        include_once('SphericalGeometry.php');
    
        class BookingsController extends BaseController {
    
             public $driver_controller;
    
    
    
             public function index()
             {
    
             }
    
    
             public function book()
             {
                 $booking = json_decode(json_encode($_GET["trip"]));
    
                 if(count(Auth::user()) > 0)
                 {
                   $booking = Auth::user()->Customer->check_agenda($booking, TRUE);
    
                    if($booking->status == FALSE)
                    {
                           return json_encode($booking);
                    }
                 }
    
                 $this->driver_controller = new DriversController;
    
                 //to do the booking we have to test wich driverts are available, // if not user must must be notified
                 $booking = $this->driver_controller->get_drivers_available_for_the_course_by_agenda($booking);
    
    
                 if(count($booking->drivers))
                 {
    
                    $booking = $this->driver_controller->get_drivers_prev_next($booking);
                    $booking = $this->driver_controller->get_drivers_that_can_do_the_course($booking);
    
    
                    $booking  =$this->driver_controller->get_offers($booking);
    
    
    
                    //now we got the drivers we wil get offers for this drivers and test is the selectec car is available
                    $available_drivers = [];
    
                    $found_offer = FALSE;
                    foreach($booking->offers as $offer)
                    {
                        if($offer->id == $booking->offer_id)
                        {
                            $found_offer  = TRUE;
                            foreach($offer->drivers as $driver)
                            {
                                 foreach($driver->cars as $car)
                                 {
                                     if($car->car_id == $booking->car_id)
                                        array_push($available_drivers, $driver);
                                 }
                            }
    
    
                            if(count($available_drivers) > 0)
                            {
    
                                $min_time_driver = NULL;
                                foreach($available_drivers as $driver)
                                {
                                      if($min_time_driver == NULL)
                                        $min_time_driver = $driver;
    
                                      if($driver->total_trip_time < $min_time_driver->total_trip_time)
                                        $min_time_driver = $driver;
                                }
    
    
                                if($min_time_driver  != NULL)
                                {
    
                                    $pre_book = new Booking;
                                    $pre_book->offer_id = $offer->id;
                                    $pre_book->company_id = $this->company->id;
    
                                    $pre_book->payed = 0;
                                    $pre_book->driver_id = $min_time_driver->id;
                                    $pre_book->car_id = $booking->car_id;
                                    $pre_book->distance = floatval($booking->distance / 1000);
                                    $pre_book->duration = floatval($booking->duration / 60);
    
                                    $pre_book->driver_departing_time = $min_time_driver->recomended_departing_time;   
                                    $pre_book->driver_arrival_time =   $booking->driver_arrival_time;   
                                    $pre_book->departing_date = $booking->departing_time;  
                                    $pre_book->arrival_date =   $booking->arrival_time;    
    
                                    $pre_book->departing_address = $booking->departing;
                                    $pre_book->departing_point_lat = $booking->dlat;
                                    $pre_book->departing_point_lat = $booking->dlng;
                                    $date = date("Y-m-d H:i:s");
    
    
    
                                    if(strlen($booking->arrival) > 0)
                                    {
                                        $pre_book->arrival_address = $booking->arrival;
                                        $pre_book->arrival_point_lat = $booking->alat;
                                        $pre_book->arrival_point_lng = $booking->alng;
                                    }
    
                                    switch(intval($offer->calc_method))
                                    {
                                        case 1:
                                        {
                                            $distance =  floatval(($booking->distance / 1000));
                                            $pre_book->trip_total  = round($distance * $offer->cost,2);
    
                                        }
                                        break;
                                        case 2:
                                        {
                                            $duration =  floatval(($booking->duration / 60));
                                            $pre_book->trip_total  = round($duration * $offer->cost, 2);
    
                                        }
                                        break;
                                        case 3:
                                        {
    
                                           $pre_book->trip_total = round(floatval($offer->cost),2);
    
                                        }
                                        break;
    
                                    }
    
                                      $pre_book->sub_total  =  $pre_book->trip_total;
                                      $pre_book->total  =   floatval($pre_book->trip_total) * (1 + floatval($this->company->tva / 100));
    
                                   if (Auth::check())
                                   {
                                       //User Exception starts at 0
                                       //User not Logged = 1
                                       //Account not active == 2
                                       $passed = TRUE;
                                       if (Auth::user()->active == 0)
                                       {
                                           $passed = FALSE;
                                           $booking->exception  = 2;
                                           $booking->exception_msg  = 'You Account is not active, please ask you administrator';
                                           return json_encode($booking);
                                       }
                                       //Customer exception start at 50 
                                        //Account is not cutomer== 10
                                       if (count(Auth::user()->Customer) < 1)
                                       {
                                           $passed = FALSE;
                                           $booking->exception  = 50;
                                           $booking->exception_msg  = 'You Account is not Customer, please create an account on the Homepage';
                                           return json_encode($booking);
                                       }
                                       //Bookings Exception starts at 400
                                        //401 -> Unknow Error
                                       //450 -> Unknow Error
                                       if($passed)
                                       {
    
                                           try
                                           {
                                               $pre_book->customer_id = Auth::user()->Customer->id;
                                               $pre_book->status = 'CREATED';
                                               $pre_book->save();
                                               $booking->status  =TRUE;
                                               $booking->exception  = 400;
                                               $booking->exception_msg  = 'Trip as been booked with success';
                                               //Once you bookin as been set on the system on can check also the payemet methods existing for the user... to more aceletar te the booking process
                                               //next view user can be informed of they payment method available status
                                               //for exmple a car can be use for 30 minutes 
    
                                               $mangopay = new MangoPay();
    
                                               $status = $mangopay->check_user_mangopay(Auth::user()->id);
                                               if($status->result)
                                               {
                                                   $status = $mangopay->get_token_card(Auth::user(), $pre_book);
    
                                                   if($status)
                                                   {
    
                                                        $booking->id  = $pre_book->id;
                                                        $booking->payment_status  = TRUE;
                                                        $booking->payment_message  = 'Payemnt Token OK';
    
                                                   }
                                                   else
                                                   {
                                                       $booking->payment_status  = FALSE;
                                                       $booking->payment_message  = 'Payement gateway not available please try later 1.';
                                                   }
                                                   return json_encode($booking);
    
                                               }
                                               else
                                               {
                                                   $passed = FALSE;
                                                   $booking->payment_status  = FALSE;
                                                   $booking->payment_message  = 'Payement gateway not available please try later 2.';
                                                   return json_encode($booking);
    
                                               }
                                               return json_encode($booking);
                                           }
    
                                           catch(Exception $ex)
                                           {
                                               $passed = FALSE;
                                               $booking->exception  = 50;
                                               $booking->exception_msg  = 'An error ocuured when processing you trip details, please try again later.';
                                               echo $ex;
                                               return json_encode($booking);
    
                                           }
                                        }
    
    
                                    }
                                    else
                                    {
                                         $booking->status  = FALSE;
                                         $booking->exception  = 1;
                                         $booking->exception_msg  = 'You are not logged, must be loggeed to procced with your booking';
    
                                         return json_encode($booking);
    
                                    }
    
                                }
                            }
                            else
                            {
    
                               //OFFERS EXPETION START at 100
                               // 105 OFFER NOT AVAILABLE ANYMORE
                               // 106 NO OFFERS MATCHING THE TRIP
                               // 110 NO CARS AVAILABLE TO DO OFFER, MENS IN HTE MEAN TIME SOMEONE AS TAKE THE TRIP
                               // 111 NO DRIVERS AVAILABLE TO DO OFFER, MENS IN HTE MEAN TIME SOMEONE AS TAKE THE TRIP
    
                               $booking->status  = FALSE;
                               $booking->exception  = 111;
                               $booking->exception_msg  == 'Driver not avaialable, will make a new search to find new availabilities,  please wait...';
                               return json_encode($booking);
                            }
    
    
                       }
                       else
                       {
    
                             $booking->status  = FALSE;
                             $booking->exception  = 105;
                             $booking->exception_msg  == 'Offer not available anymore';
                             return json_encode($booking);
                       }
                  }
    
    
                  if($found_offer == FALSE)
                  {
                       $booking->status  = FALSE;
                       $booking->exception  = 106;
                       $booking->exception_msg  == 'No Offers Matching the trip';
                       return json_encode($booking);
    
                  }
    
    
             }
             }
    
          public function bookings_by_interval($id, $interval, $type)
          {
    
    
    
            if(isset($id) && isset($interval))
            {
                $bookings = [];
                if($type =='customer')
                {
    
                $sql="select CONCAT(users.first_name,' ' ,users.last_name) as full_name, 
                       bookings.id,
                       bookings.departing_date,
                       false as allDay,
                       bookings.departing_date as start,
                       bookings.arrival_date as end,
                       bookings.arrival_date,
                       bookings.duration,
                       bookings.distance,
                       bookings.total,
                       CONCAT(bookings.departing_address,' => ',bookings.arrival_address)  as title,
                       bookings.departing_address,
                       bookings.arrival_address,
                       bookings.status,
                       get_booking_title(offers.trip_method, offers.calc_method, bookings.departing_address, bookings.arrival_address, bookings.distance, bookings.duration, bookings.total) as title,
                       offers.trip_method,
                       offers.calc_method,
                       CONCAT(cars.brand,' ', cars.model) as car_name,
                       cars.img as car_img
                       from `users` 
                       inner join customers as customers on (users.id = customers.user_id)
                       inner join bookings on (customers.id = bookings.customer_id)
                       inner join offers on (offers.id = bookings.offer_id)
                       inner join drivers on (drivers.id = bookings.driver_id)
                       inner join cars on (cars.id = bookings.car_id) ";
                }
                else
                {
                      $sql="select CONCAT(users.first_name,' ' ,users.last_name) as full_name, 
                       bookings.id,
                       bookings.departing_date,
                       false as allDay,
                       bookings.driver_arrival_time as start,
                       bookings.arrival_date as end,
                       bookings.arrival_date,
                       bookings.duration,
                       bookings.distance,
                       bookings.total,
                       CONCAT(bookings.departing_address,' => ',bookings.arrival_address)  as title,
                       bookings.departing_address,
                       bookings.arrival_address,
                       bookings.status,
                       get_trip_title(offers.trip_method, offers.calc_method, bookings.departing_address, bookings.arrival_address, bookings.distance, bookings.duration, bookings.total) as title,
                       offers.trip_method,
                       offers.calc_method,
                       CONCAT(cars.brand,' ', cars.model) as car_name,
                       cars.img as car_img
                       from `users` 
                       inner join customers as customers on (users.id = customers.user_id)
                       inner join bookings on (customers.id = bookings.customer_id)
                       inner join offers on (offers.id = bookings.offer_id)
                       inner join drivers on (drivers.id = bookings.driver_id)
                       inner join cars on (cars.id = bookings.car_id) ";
    
    
    
                }
    
    
                switch($interval)
                {
    
    
                    case 'day':
                    {
                        $today = date('Y-m-d');
                        if($type =='customer')
                           $sql .= "where customers.id = ".$id." and date(bookings.departing_date) = '".$today."'";
                        else
                           $sql .= "where drivers.id = ".$id." and date(bookings.departing_date) = '".$today."'";
    
    
    
                        $bookings  =  DB::select($sql);
    
                    }
                    break;
    
                    case 'week':
                        $week = date('W');
                        if($type =='customer')
                           $sql .= "where customers.id = ".$id." and  WEEK(departing_date, 3) = '".$week."'";
                        else
                           $sql .= "where drivers.id = ".$id." and  WEEK(departing_date, 3) = '".$week."'";
    
                        $bookings  =  DB::select($sql);
    
                    break;
    
                    case 'month':
                    {
    
                        $month =  intval(date('m'));
                        if($type =='customer')
                           $sql .= "where customers.id = ".$id." and   MONTH(departing_date) = '".$month."'";
                        else
                           $sql .= "where drivers.id = ".$id." and   MONTH(departing_date) = '".$month."'";
    
                        $bookings  =  DB::select($sql);
                    }
                    break;
    
                    default:
                    {  
                        if($type =='customer')
                           $sql .= "where customers.id = ".$id;
                        else
                           $sql .= "where drivers.id = ".$id;
    
    
                        $bookings  =  DB::select($sql);
                    }
                    break;
                }
    
    
                $this->Jtable($bookings);
    
    
            }
            else
                return NULL;
    
    
        }
    
          public function driver_bookings()
          {
    
              $bookings = [];
    
            if($this->check_driver())
            {
    
    
                $interval = "day";
    
                if(isset($_GET["interval"]))
                    $interval = $_GET["interval"];
    

                   
                      $id = Auth::User()->Driver->id;
    
                      $sql="select CONCAT(users.first_name,' ' ,users.last_name) as full_name, 
                       bookings.id,
                       bookings.departing_date,
                       false as allDay,
                       bookings.driver_arrival_time as start,
                       bookings.arrival_date as end,
                       bookings.arrival_date,
                       bookings.duration,
                       bookings.distance,
                       bookings.total,
                       CONCAT(bookings.departing_address,' => ',bookings.arrival_address)  as title,
                       bookings.departing_address,
                       bookings.arrival_address,
                       bookings.status,
                       get_trip_title(offers.trip_method, offers.calc_method, bookings.departing_address, bookings.arrival_address, bookings.distance, bookings.duration, bookings.total) as title,
                       offers.trip_method,
                       offers.calc_method,
                       CONCAT(cars.brand,' ', cars.model) as car_name,
                       cars.img as car_img
                       from `users` 
                       inner join customers as customers on (users.id = customers.user_id)
                       inner join bookings on (customers.id = bookings.customer_id)
                       inner join offers on (offers.id = bookings.offer_id)
                       inner join drivers on (drivers.id = bookings.driver_id)
                       inner join cars on (cars.id = bookings.car_id) ";
    
    
    
    
    
                $interval = "";
    
                if(isset($_GET["interval"]))
                    $interval = $_GET["interval"];
    
                switch($interval)
                {
    
    
                    case 'today':
                    {
                        $today = date('Y-m-d');
    
                        $sql .= "where drivers.id = ".$id." and date(bookings.departing_date) = '".$today."'";
    
                        $bookings  =  DB::select($sql);
    
                    }
                    break;
    
                    case 'tomorow':
                    {
    
                        $today = date('Y-m-d');
                        $tomorow = date('Y-m-d', strtotime($today. ' + 1 days'));
    
                         $sql .= "where drivers.id = ".$id." and date(bookings.departing_date) = '".$tomorow."'";
    
                        $bookings  =  DB::select($sql);
                    }
                    break;

                    case 'week':
                    {
                        $week = date('W');
    
                        $sql .= "where drivers.id = ".$id." and  WEEK(departing_date, 3) = '".$week."'";
    
                        $bookings  =  DB::select($sql);
                    }
                    break;
    
                    case 'month':
                    {
    
                        $month =  intval(date('m'));
    
                        $sql .= "where drivers.id = ".$id." and   MONTH(departing_date) = '".$month."'";
    
                        $bookings  =  DB::select($sql);
                    }
                    break;
    
                    default:
                    {  
    
                        $sql .= "where drivers.id = ".$id;
    
                        $bookings  =  DB::select($sql);
                    }
                    break;
               }
    
          }
    
            $this->Jtable($bookings);
        }
    
    }
    
        class booking_status
        {
    
            public $booking  = NULL;
            public $status  = NULL;
            public $status_code  = NULL; // 1 - complete, // 2- reserve, // 3 -chauffer indispobible // 4 - tarif indisponible pour le moment // 5  voture indispoble  // 6system error
            public $description  = NULL;
            public $datetime   = NULL;
    
            public function __construct()
            {
               $this->datetime = date('Y-m-d H:i:s');
            }
    
        }
?>

