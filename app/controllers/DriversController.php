<?php
         include_once('SphericalGeometry.php');
    
         class DriversController extends BaseController {
    
                        public $departing;
                        public $driver_departing_time;
                        public $arrival;
                        public $distance;
                        public $time;
                        public $departing_time;
                        public $arrival_time;
                        public $minutes;
                        public $kms;
                        public $day;
                        public $today;
                        public $lat;
                        public $lng;
                        //driver agenda globals
                        public $start_hour;                                                                                                                                                                                                                                                                                  
                        public $end_hour;
                        //calc method
                        public $calc_method;
                        public $trip_method;
    
                        //deparing coordinates
                        public $dlat= NULL;
                        public $dlng = NULL;
    
                        //arrival coordinates
                        public $alat= NULL;
                        public $alng = NULL;
    
                        public  $total_trip_time  = 0;
                        public  $total_trip_distance  = 0;
    
    
                        //set default kms and minute cost 
                        public $km_unit = 2;
                        public $minute_unit = 2;
    
                        public $trys;
    
    
             public function dashboard()
             {
                             return View::make('drivers.index');
             }
    
             public function index()
             {  
                 if(Auth::User()->is_super_admin())
                     $drivers =  Driver::with('user')->where('active','=', true)->get();
                 else
                 {
                     $company_id = $this->company->id;
                     $drivers = Driver::with('user')->whereHas('user', function($q) use ($company_id)
                                 {
                                     $q->where('company_id', '=', $company_id);
    
                                 })->where('active','=', true)->get();
                 }
    
                 $this->Jtable($drivers);
             }
    
    
               function oauth2callback()
                        {
    
                            echo ' {"web":{"auth_uri":"https://accounts.google.com/o/oauth2/auth","client_secret":"cVFJdpmg7vCarDUeDnFfh9ZE","token_uri":"https://accounts.google.com/o/oauth2/token","client_email":"656391148071-a2ai7qrb7v0jb1gq09r1alsd1gul2pno@developer.gserviceaccount.com","redirect_uris":["http://localhost:8080/oauth2callback"],"client_x509_cert_url":"https://www.googleapis.com/robot/v1/metadata/x509/656391148071-a2ai7qrb7v0jb1gq09r1alsd1gul2pno@developer.gserviceaccount.com","client_id":"656391148071-a2ai7qrb7v0jb1gq09r1alsd1gul2pno.apps.googleusercontent.com","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","javascript_origins":["http://localhost:8080/"]}}';
                        }
    
    
                function compute_distance()
                {
                    $SphericalGeometry = new SphericalGeometry();
                    if(isset($_GET['point_a']))
                        $point_a =  json_decode($_GET['point_a']);

                    if(isset($_GET['point_b']))
                        $point_b =  json_decode($_GET['point_b']);

                    
                    $a_point = new LatLng($point_a->lat, $point_b->lng, false);
                    $b_point = new LatLng($point_a->lat, $point_b->lng, false);
                    $distance =  $SphericalGeometry->computeDistanceBetween($a_point, $b_point);
                    
                    
                    return $distance;
    
               }
    

               
                function compute_distance2($point_a, $point_b)
                {
                    $SphericalGeometry = new SphericalGeometry();
                
                    $distance =  $SphericalGeometry->distance($point_a, $point_b);
                    
                    
                    return $distance;
    
               }
                function get_less_time_route($routes)
                {
    
                           $less_time_route = NULL;
    
                           foreach($routes as $route)
                           {
                               if($less_time_route == NULL)
                                 $less_time_route = $route;
    
                               if($route->legs[0]->duration->value < $less_time_route->legs[0]->duration->value)
                                 $less_time_route = $route;
                           }
    
                           return  $less_time_route;
    
                }
    
                public function trip_find_availablibility()
                 {  
                          $route_origin= NULL;
    
                          $booking = json_decode(json_encode($_GET["trip"]));
    
                        
    
                           // for the case of A TRIP O A --> B
                           // we dont know the duration of the course to evaluate the agenda of each driver
                           //because we have to if the driver is available for a course.
                           //base of alghorithm
    
                           //STEP 1
                           //we find drivers that have a disponiblities from the departing time to the arrival _time
                           //if a customer wants to from paris  at 10:00, we get from google apis the distance and duration  ex: 30 min
                           //and we will find all driver that dont have any nothing to do from 10:00 to 10:30
    
    
                           $booking = $this->get_drivers_available_for_the_course_by_agenda($booking);
    
    
                          if(count(Auth::user()) > 0)
                          {
                             $booking = Auth::user()->Customer->check_agenda($booking, TRUE);
                                if($booking->status == FALSE)
                                {
                                    echo json_encode($booking);
                                    return;
                                }
                          }
    
    
                           //STEP 2
                           //because a driver can have diferet positions, on garage or on the street (in, out)
                           //in -> means not working or on the gare, garage
                           //out means is doing a job, or is just stoped somewhere waiting for a new trip, or at least is comming home or in
                           // the fact of a driver has availability from 10:: to 10:30 does means thaa is conditions to do the job, cause i we said we drivers can be somehere
                           //and we dont know if the driver has time to arrive to trip proposed from the system
                           //so we have have to set the for the drivers a the last and the next position
                           //The RULE is follow:
                           //To calc the time to arrive to the the current trip, we have to know the last postion, if a driver is on garage is postion is in
                           //but we have to know also the next postion, cause driver can have time to arrive but no time to make the next trip
                           //to KNOW we calc
                           //the time we have to do the follow cals
                           //TIME FROM PREVIOUS POSTITION TO CURRENT
                           //TIME TO MAKE THE COURSE
                           //TIME TO ARRIVE TO NEXT COURSE
    
                          // so will set the prev and next positons
                          //this method foes to database and look if driver has a trip before and trip after if not that menas is home
                          //Driver can have a trip before out and a trip after out
                          //Driver can have also a trip before in (Menas is on garage) and a trip after out
                          //Driver can have also a trip before out and a trip after oto garage in
                          //driver can also a have a trip before in (emans is on garage) and a trip after( on garage) in this case menas that ones thas not have any job at the current time
    
                          $booking = $this->get_drivers_prev_next($booking);
     
                           //now we get the positions we gonna to the calcultaion to find each one is capable from the the current position to arrive at time to the trip
                           // and also if is capable to arrive on time to next trip
                          $booking = $this->get_drivers_that_can_do_the_course($booking);
                          $booking->tva = $this->company->tva; 
    
                          $booking  = $this->get_offers($booking);
                          echo json_encode($booking);
    
                }
  
    
    
    
    
                function get_geo_info_from_A_to_B($point_a, $point_b)
                {
                            $trip_info = new TripInfo();
                            $trip_info->departing = $point_a;
                            $trip_info->arrival = $point_b;
    
                            $param = array("origin"=> $point_a , "destination" =>  $point_b);
                            $reponse = Geocoder::directions('json', $param);
    
                             if($reponse != NULL)
                             {
                                  $result = json_decode($reponse);
    
                                  $route = $this->get_less_time_route($result->routes);
    
                                  if($route != NULL)
                                  {
                                      $trip_info->distance = $route->legs[0]->distance->value;
                                      $trip_info->duration = $route->legs[0]->duration->value;
                                      $trip_info->route = $route;
                                  }
                                  else
                                  {
                                      $this->trys = $this->trys +1;
                                      if( $this->trys < 3)
                                         $this->get_geo_info_from_A_to_B($point_a, $point_b);
                                  }
                             }
    
                             return $trip_info;
                 } 
    
    
    
                 function get_drivers_available_for_the_course_by_agenda($booking)
                 {
    
    
                     $booking->driver_arrival_time = date('Y-m-d H:i:s',strtotime('-15 minutes', strtotime($booking->departing_time)));     
                     $booking->today = date('Y-m-d',strtotime($booking->driver_arrival_time));
    
                     $booking->day =   date('D',strtotime($booking->driver_arrival_time));
                     $booking->today_end = date('Y-m-d H:i:s',strtotime($booking->today.' 23:59:59'));
                     $booking->duration = 0;
    
                     if(isset($booking->arrival) && strlen($booking->arrival) > 0)
                     {
    
                         $param = array("origin"=> $booking->departing, "destination" => $booking->arrival);
                         $reponse = Geocoder::directions('json', $param);
    
                         if($reponse != NULL)
                         {
    
                             $result = json_decode($reponse);
    
                             if($result != NULL)
                             {  
    
                                 $route = $this->get_less_time_route($result->routes);
    
    
                                 $booking->duration = $route->legs[0]->duration->value;
                                 $booking->distance = $route->legs[0]->distance->value;
                                 $booking->arrival_time =  date('Y-m-d H:i:s',strtotime('+'. $booking->duration.' seconds', strtotime($booking->departing_time))); 
    
                                 $booking->departing_date = date('Y-m-d',strtotime($booking->driver_arrival_time));
                                 $booking->arrival_date = date('Y-m-d',strtotime($booking->arrival_time));
    
    
                                 $booking->start_hour = date('H:i',strtotime($booking->driver_arrival_time));
                                 $booking->end_hour =  date('H:i',strtotime($booking->arrival_time));
    
    
                                 //echo $booking->arrival_time.' | '.$booking->today_end;
                                 if(strtotime($booking->arrival_time) > strtotime($booking->today_end))
                                 {
                                          $booking->today_end_hour = date('H:i',strtotime($booking->today_end));
                                          $booking->next_day = date('Y-m-d',strtotime($booking->arrival_time));
                                          $booking->next_day_hour = date('H:i',strtotime($booking->next_day .' 00:00:00'));
                                          $booking->day_next = date('D',strtotime($booking->next_day));
    
    
                                        $sql ='select drivers.id, drivers.user_id, drivers.avatar_url, drivers.created_at,
                                             drivers.updated_at ,users_times.start, users_times.end from drivers 
                                             inner join users_times on (drivers.user_id = users_times.user_id) 
                                             inner join users on (users.id = drivers.user_id) 
                                             where (users_times.start <= "'.$booking->start_hour.'" and users_times.`end` >= "'.$booking->today_end_hour.'" and users_times.`day_week` ="'.$booking->day.'") and users.company_id='.$this->company->id.' 
                                             and drivers.active=true  and drivers.id IN(select drivers.id  from drivers inner join users_times on (drivers.user_id = users_times.user_id) 
                                             where  (users_times.start <= "'.$booking->next_day_hour.'" and users_times.`end` >= "'.$booking->end_hour.'" and users_times.`day_week` ="'.$booking->day_next.'")) 
                                             and drivers.id NOT IN(select bookings.driver_id from bookings
                                             where (bookings.driver_arrival_time between "'.$booking->driver_arrival_time .'" and "'.$booking->arrival_time.'" and bookings.company_id='.$this->company->id.' ) or
                                             (bookings.arrival_date between "'.$booking->driver_arrival_time .'" and  "'.$booking->arrival_time.'" and bookings.company_id='.$this->company->id.') or
                                             (bookings.driver_arrival_time <= "'.$booking->driver_arrival_time .'" and bookings.arrival_date >= "'.$booking->arrival_time.'" and bookings.company_id='.$this->company->id.') group by driver_id)';
    
    
    
                                 }
                                 else
                                 { 
    
                                        $sql ='select drivers.id, drivers.user_id, drivers.avatar_url, drivers.created_at,
                                                 drivers.updated_at  ,users_times.start, users_times.end from drivers 
                                                 inner join users_times on (drivers.user_id = users_times.user_id)
                                                  inner join users on (users.id = drivers.user_id) 
                                                 where (users_times.start <= "'.$booking->start_hour.'" and users_times.`end` >= "'.$booking->end_hour.'" and users_times.`day_week` ="'.$booking->day.'" ) 
                                                 and users.company_id='.$this->company->id.' and drivers.active=true and drivers.id NOT IN(select bookings.driver_id  from bookings
                                                 where (bookings.driver_arrival_time between "'.$booking->driver_arrival_time .'" and "'.$booking->arrival_time.'" and bookings.company_id='.$this->company->id.' ) or
                                                 (bookings.arrival_date between "'.$booking->driver_arrival_time .'" and  "'.$booking->arrival_time.'" and bookings.company_id='.$this->company->id.') or
                                                 (bookings.driver_arrival_time <= "'.$booking->driver_arrival_time .'" and bookings.arrival_date >= "'.$booking->arrival_time.'" and bookings.company_id='.$this->company->id.') group by driver_id)';
    
    
    
                                  }
    
                                 $booking->drivers = DB::select($sql);
                                 }
                             }
                         }
                     else
                     {
                         if($booking->calc_method == 1)
                         {
                             $booking->duration = 1.2 * $booking->kms * 60;
                             $booking->distance = $booking->kms * 1000;
                         }
                         else
                         {
                             $booking->duration = $booking->minutes * 60;
                             $booking->distance = 1.6 * $booking->minutes * 1000;
                         }
    
                             $booking->arrival_time =  date('Y-m-d H:i:s',strtotime('+'. $booking->duration.' seconds', strtotime($booking->departing_time))); 
    
                             $booking->departing_date = date('Y-m-d',strtotime($booking->driver_arrival_time));
                             $booking->arrival_date = date('Y-m-d',strtotime($booking->arrival_time));
    
                             $booking->start_hour = date('H:i',strtotime($booking->driver_arrival_time));
                             $booking->end_hour =  date('H:i',strtotime($booking->arrival_time));
    
                          $sql='';
                          if(strtotime($booking->arrival_time) > strtotime($booking->today_end))
                          {
    
    
                             $booking->today_end_hour = date('H:i',strtotime($booking->today_end));
                             $booking->next_day = date('Y-m-d',strtotime($booking->arrival_time));
                             $booking->next_day_hour = date('H:i',strtotime($booking->next_day .' 00:00:00'));
                             $booking->day_next = date('D',strtotime($booking->next_day));
    
    
                                        $sql ='select drivers.id, drivers.user_id, drivers.avatar_url, drivers.created_at,
                                             drivers.updated_at ,users_times.start, users_times.end from drivers inner join users_times 
                                             on (drivers.user_id = users_times.user_id)
                                              inner join users on (users.id = drivers.user_id)  
                                             where (users_times.start <= "'.$booking->start_hour.'" and users_times.`end` >= "'.$booking->today_end_hour.'" and users_times.`day_week` ="'.$booking->day.'") 
                                             and drivers.active=true and users.company_id='.$this->company->id.' and drivers.id IN(select drivers.id  from drivers inner join users_times on (drivers.user_id = users_times.user_id) 
                                             where  (users_times.start <= "'.$booking->next_day_hour.'" and users_times.`end` >= "'.$booking->end_hour.'" and users_times.`day_week` ="'.$booking->day_next.'")) 
                                             and drivers.id NOT IN(select bookings.driver_id from bookings
                                             where (bookings.driver_arrival_time between "'.$booking->driver_arrival_time .'" and "'.$booking->arrival_time.'" and bookings.company_id='.$this->company->id.') or
                                             (bookings.arrival_date between "'.$booking->driver_arrival_time .'" and  "'.$booking->arrival_time.'"  and bookings.company_id='.$this->company->id.') or
                                             (bookings.driver_arrival_time <= "'.$booking->driver_arrival_time .'" and bookings.arrival_date >= "'.$booking->arrival_time.'" and bookings.company_id='.$this->company->id.') group by driver_id)';
    
    
                          }
                          else
                          {
    
                             $sql ='select drivers.id, drivers.user_id, drivers.company_id, drivers.avatar_url, drivers.created_at,
                                     drivers.updated_at  ,users_times.start, users_times.end from drivers 
                                     inner join users_times on (drivers.user_id = users_times.user_id)
                                     inner join users on (users.id = drivers.user_id) 
                                     where (users_times.start <= "'.$booking->start_hour.'" and users_times.`end` >= "'.$booking->end_hour.'" and users_times.`day_week` ="'.$booking->day.'") 
                                     and drivers.active=true and users.company_id='.$this->company->id.' and drivers.id NOT IN(select bookings.driver_id  from bookings
                                     where (bookings.driver_arrival_time between "'.$booking->driver_arrival_time .'" and "'.$booking->arrival_time.'" and bookings.payed = 1 and bookings.company_id='.$this->company->id.') or
                                     (bookings.arrival_date between "'.$booking->driver_arrival_time .'" and  "'.$booking->arrival_time.'  and bookings.payed = 1" and bookings.company_id='.$this->company->id.') or
                                     (bookings.driver_arrival_time <= "'.$booking->driver_arrival_time .'" and bookings.arrival_date >= "'.$booking->arrival_time.'"  and bookings.payed = 1 and bookings.company_id='.$this->company->id.') group by driver_id)';
    
    
                         }
                         $booking->drivers = DB::select($sql);
                     }
    
                     return $booking;
    
                 }
    
    
    
                 function get_drivers_prev_next($booking)
                 {
    
                     foreach($booking->drivers as $driver)
                     { 
    
                         //we need the default driver point when drivers has no trips to do, tis means that driver should be on the garage (company)
                         $prev = new MapPoint($this->company->lat, $this->company->lng, $this->company->address, $booking->today.' '.$driver->start, 'in');
                         $next = new MapPoint($this->company->lat, $this->company->lng, $this->company->address, $booking->today.' '.$driver->end , 'in');
    
    
                         //will find first booking before
                         $booking_before = Booking::where('driver_id','=',$driver->id)
                               ->where('arrival_date','>=',  $booking->departing_date) // all bookings where arrival is to
                               ->where('payed','=', 1)
                               ->where('arrival_date','<', $booking->driver_arrival_time)
                               ->orderBy('arrival_date', 'desc')->first();
    
    
    
                         //will find booking next
                         $booking_after = Booking::where('driver_id','=',$driver->id)
                             ->where('driver_arrival_time','>', $booking->arrival_date)
                             ->where('payed','=', 1)
                             ->where('driver_arrival_time','>=',  $booking->driver_arrival_time)->orderBy('driver_arrival_time', 'asc')->first();
    
    
                          //if user has not a booking before for today that means that he is one the garage
                         if(count($booking_before) ==1)
                               $prev = new MapPoint($booking_before->arrival_point_lat, $booking_before->arrival_point_lng, $booking_before->arrival_address, $booking_before->arrival_date, 'out');
    
                         if(count($booking_after)==1)
                                $next = new MapPoint($booking_after->departing_point_lat, $booking_after->departing_point_lng, $booking_after->departing_address, $booking_after->driver_arrival_time,'out');
    
    
    
                         $driver->prev = $prev;
                         $driver->next = $next;
    
                         if(count($booking_before) == 1 || count($booking_after) ==1)
                         {
    
                             if(count($booking_before) ==1)
                                 $driver->cars =  Car::find($booking_before->car_id)->first();
                             else
                                $driver->cars =  Car::find($booking_after->car_id)->first();
    
                         }
                         else
                         {
                                $driver->cars  = [];
    
                         }   
    
                     }
    
                     return $booking;
                 }
    
    
    
                function get_drivers_that_can_do_the_course($booking)
                { 
                     $drivers_available =[];
                     foreach($booking->drivers as $driver)
                     {   
    
    
                                  //we have to save total spent time for each driver
                                  $total_time  = 0;
                                  $total_distance  = 0;
    
    
    
                                  // We get the time that the driver takes to arrive to the departing location
                                  $trip_info_departing = $this->get_geo_info_from_A_to_B($driver->prev->location, $booking->departing); 
    
    
                                  $total_time =  $total_time + $trip_info_departing->duration + $booking->duration; 
                                  $total_distance =  $total_distance + $trip_info_departing->distance + $booking->distance; 
    
                                  //  $total_time =  $total_time + $trip_info_departing->duration + $booking->duration; 
                                  //$total_distance =  $total_distance + $trip_info_departing->distance + $booking->distance; 
                                  //we get the driver arrival time by using the duration of the course
                                  $driver->recomended_departing_time =  date('Y-m-d G:i:s',  strtotime('-'.$trip_info_departing->duration.' seconds', strtotime($booking->driver_arrival_time)));
    
    
                                  //if the arrival date is before the departing time we follow the algorithm else we discard this driver
                                  if(strtotime($driver->recomended_departing_time) >= strtotime($driver->prev->time)) 
                                  {
    
                                      //Now we have to check if i got the time to make the course and arrive to the enxt course
                                      //by this we have to check how much is from the customer arrival place to the next trip
                                      // so if trip next is out ->menas user has a next trip we check
                                      $time_to_arrive_next = NULL;
                                      $is_driver_able_to_arrive = FALSE;
    
                                      $trip_duration = NULL;
                                      if(empty($this->arrival))
                                      { 
    
    
    
                                              $trip_info_next = $this->get_geo_info_from_A_to_B($booking->departing, $driver->next->location); 
                                              //time to arrive to next
                                              //if we dont know arrival that means time will  the time of rent * 2 to give time to comme back to departing position
                                              //beacuse this is the unique position that we have has reference.., from there we summ the time to arrive to next
                                              // and we get an estimation of time that can arrive and max, at minimum we dont know at moment
                                              $time_to_arrive_next =  date("Y-m-d G:i:s", strtotime("+".($trip_info_next->duration +  ($booking->duration * 2))." seconds" , strtotime($booking->departing_time)));
    
                                      }
                                      else
                                      {        
                                         $trip_info_next = $this->get_geo_info_from_A_to_B($booking->arrival, $driver->next->location); 
                                         $time_to_arrive_next =  date("Y-m-d G:i:s", strtotime("+".($trip_info_next->duration)." seconds", strtotime($booking->arrival_time)));
                                      }  
    
                                      if($booking->trip_method == 'TAB')
                                      {
                                             $driver->total_trip_time = $trip_info_departing->duration  + $booking->duration  + $trip_info_next->duration;
                                             $driver->total_trip_distance = $trip_info_departing->distance  + $booking->distance  + $trip_info_next->distance;
                                      }
                                      else
                                      {
                                             $driver->total_trip_time = $trip_info_departing->duration  + ($booking->duration  * 2) + $trip_info_next->duration;
                                             $driver->total_trip_distance = $trip_info_departing->distance  + ($booking->distance * 2)  + $trip_info_next->distance;
                                      }
    
    // echo $time_to_arrive_next.'|'.$driver->next->time.'</br>';
                                     if(strtotime($time_to_arrive_next) < strtotime($driver->next->time) || $driver->next->tag == 'in')
                                             $is_driver_able_to_arrive = TRUE;
    
    
    
    
                                     if($is_driver_able_to_arrive)
                                     {
    
                                         //set driver cars 
                                         if(count($driver->cars) > 0)
                                         {
    
                                             $cars =  Car::find($driver->cars->id)->first();
                                             array_push($driver->cars , $cars);
                                         }
                                         else
                                         {
    
    
                                             //if a driver has not a car that means he has no books in day of the trip, so we is on garage
                                             //if we is on garage wew can talke any car that are not used 
                                             //this cars atha user can drive, the cars that arrived before the course 
                                             //all cars where driver_departing_time is less then the trip are used
                                             //all cars where driver_departing_time i less then arrival_time  are used
                                             // so we take all cars that are not used  before the end of the trip
                                             $sql='select * from cars 
                                                 inner join drivers_cars on (cars.id= drivers_cars.car_id) 
                                                 inner join drivers on (drivers.id = drivers_cars.driver_id)
                                                 where drivers_cars.car_id 
                                                 NOT IN( select bookings.car_id from bookings where bookings.driver_departing_time < "'.$booking->arrival_time.'" 
                                                 and bookings.arrival_date >= "'.$driver->recomended_departing_time.'" and bookings.driver_id !='.$driver->id.') and cars.company_id = '.$this->company->id.' and drivers_cars.driver_id='.$driver->id.' group by drivers_cars.car_id';
    
    
                                             $driver->cars = [];
                                             $cars = DB::select($sql);
                                             $driver->cars = $cars;
                                         }
    
                                         if(count($driver->cars) > 0)
                                             array_push($drivers_available , $driver);
                                     }
    
                               }
                          }
    
                     //$driver_lower_time = NULL;
    
                     //foreach($booking->drivers as $driver)
                     //{
                     //    if($driver_lower_time == NULL)
                     //        $driver_lower_time = $driver;
                     //    
                     //    if( $driver->total_trip_time < $driver_lower_time->total_trip_time)
                     //        $driver_lower_time = $driver;
    
                     //}
    
                     //$booking->drivers = [];
                     //array_push($booking->drivers, $driver_lower_time);
                     $booking->drivers = $drivers_available;
                     return $booking; 
    
                }
    
    
                function get_offers($booking)
                {
                    $all_offers = [];
    
                 // echo json_encode($booking->drivers);
                    foreach($booking->drivers as $driver)
                    {
    
    
                         $cars  =[];
    
                         foreach($driver->cars as $car)
                             array_push($cars, $car->car_id);
    
    
                        $cars_string =  implode(",", $cars);
    
                        $sql = "select * from offers 
                                inner join offers_drivers as drivers on (drivers.offer_id = offers.id)
                                inner join offers_cars as cars on (cars.offer_id = offers.id)
                                where drivers.driver_id IN (".$driver->id.")
                                and cars.car_id IN (".$cars_string.") and  offers.company_id=".$this->company->id." group by offers.id";
    
    
    
                        $offers = DB::select($sql);
    

                       
                        foreach($offers as $offer)
                        {
    
                               $found_offer = FALSE;
                               foreach($all_offers as $existing_offer)
                               {
    
                                   if($existing_offer->id == $offer->id)
                                   {
                                        $found_offer = TRUE;
    
                                        foreach($driver->cars as $car)
                                        {
    
                                            $offer_car = OfferCar::where('car_id', '=', $car->id)
                                                ->where('offer_id', '=', $offer->id)->get()->first();
    
    
    
                                            if(count($offer_car) > 0)
                                            {
    
                                                $fount_car = FALSE;
    
                                                foreach($existing_offer->cars as $existing_car)
                                                {  
    
                                                     if($existing_car->id == $offer_car->Car->id)
                                                        $fount_car = TRUE;
    
                                                }
    
                                                if($fount_car == FALSE)
                                                   array_push($existing_offer->cars, $offer_car->Car);
                                            }
    
                                        }
    
                                        array_push($existing_offer->drivers , $driver);
    
                                   }
    
    
                               }
    
                               if($found_offer == FALSE)
                               {     
                                   $offer->cars = [];
                                   $offer->drivers = [];
                                   array_push($offer->drivers, $driver);
                                   foreach($driver->cars as $car)
                                   {
                                       $offer_car = OfferCar::where('car_id', '=', $car->car_id)
                                                ->where('offer_id', '=', $offer->id)->get()->first();
    
    
                                       if(count($offer_car) > 0)
                                       {
    
                                           array_push($offer->cars , $offer_car->Car);
                                       }
                                   }
    
                                   array_push($all_offers, $offer);
                                  
                               }
    
                        }
    
    
                    }
    
    
                   $booking->offers = [];
                   $offre_prioritaires = [];
                   $offre_normal = [];
    
                   foreach($all_offers as $offer)
                   {
                         $reference_point = new  LatLng($offer->dlat, $offer->dlng);
                         $point = new  LatLng($booking->dlat,  $booking->dlng);
                         $distance_from_departing_offer = $this->compute_distance2($reference_point, $point);
    
                         //echo $booking->trip_method.'-'.$offer->trip_method;
                         if($booking->trip_method == 'TAB' && $offer->trip_method == 'TAB')
                         {
    
                              $reference_point = new  LatLng($offer->alat, $offer->alng);
                              $point = new  LatLng($booking->alat,  $booking->alng);
                              $distance_from_arrival_offer = $this->compute_distance2($reference_point, $point);
                              //echo $offer->title.'->'.$distance_from_departing_offer.'--'.$offer->radiusd.'   |   '.$distance_from_arrival_offer.' | '.$offer->radiusa.'</br>';
    
                              if($distance_from_departing_offer < $offer->radiusd && $distance_from_arrival_offer < $offer->radiusa)
                                  array_push($offre_prioritaires, $offer);
    
                              if($distance_from_departing_offer < $offer->radiusd && $distance_from_arrival_offer > $offer->radiusa)
                                  array_push($offre_normal, $offer);
                         }
                         else
                         {      
                             if($distance_from_departing_offer < $offer->radiusd)
                             {   
    
    
    
                                 //Si le book et du ype TAB ça vveux dire ici que OFFRE es d'un autre type, donc elle n'es pas prioritaire
                                 if($booking->trip_method == 'TAB')
                                     array_push($offre_normal, $offer);
                                 else
                                 {
                                     // se l'ffre et du type TAB alors le voayge n'es pas alors, sinon l'ffre es du mem type
                                      if($offer->trip_method == 'TAB')
                                         array_push($offre_normal, $offer);
                                      else
                                        array_push($offre_prioritaires, $offer);
    
                                 } 
                             }
                         }
    
                   }
    
                  foreach($offre_prioritaires as $offer)
                     array_push($booking->offers, $offer);
    
                  foreach($offre_normal as $offer)
                     array_push($booking->offers, $offer);
    
    
                  return $booking;
                }
    
                 function set_drivers_times($drivers_data)
                 {
                     $drivers = [];
                            foreach($drivers_data as $driver)
                            {
    
                                    $db_driver = Driver::find($driver->id);
                                    if($db_driver  != NULL)
                                    {
                                        $db_driver->start  = $driver->start;
                                        $db_driver->end  = $driver->end;
    
                                        array_push($drivers, $db_driver);
                                    } 
                             }
    
                             return $drivers;
                 }
    
                 function get_average_drivers($drivers , $best_driver, $best_drivers)
                 {
    
                           foreach($drivers as $driver)
                           {
    
                               //echo 'iD '.$driver->id;
                              $distance_margin = $best_driver->total_time_plus + 900;
                              ///  echo 'original '.$driver->total_time_plus.' and '.$distance_margin.' </br>';
    
                                 if($driver->total_time_plus <= $distance_margin && $driver->id != $best_driver->id)
                                    array_push($best_drivers, $driver);
                           }
    
                           return  $best_drivers;
                  }
    
    
                 function get_best_driver_trip($drivers)
                        {
                           $fastest_driver = NULL;
                           foreach($drivers as $driver)
                           {
                               if($driver->availability == 'TRIP')
                               {
                                   if($fastest_driver == NULL)
                                        $fastest_driver = $driver;
    
                                        if($driver->total_time_plus <  $fastest_driver->total_time_plus)
                                         $fastest_driver = $driver;
                               }
                           }
    
                           return $fastest_driver;
    
                        }
    
    
                        //Get default trips
    
    
                 function Trip_AB($departing, $arrival)
                        {
                                  $default_offers=[];
    
    
                                 //TRAJECT NORMAL A > B
                                 $offers_data = new Offer();
                                 $offers_data->title = 'Trajects facturé a lá Minute Depuis '.$departing.' jusque á '.$arrival;
                                 $offers_data->description  = 'Trajects facturé a lá Minute Depuis '.$departing.' jusque á '.$arrival;
                                 $offers_data->departing = $departing;
                                 $offers_data->dlat = NULL;
                                 $offers_data->dlng = NULL;
                                 $offers_data->radiusd= NULL;
                                 $offers_data->arrival = $arrival;;
                                 $offers_data->alat = NULL;
                                 $offers_data->dlng =  NULL;
                                 $offers_data->trip_method = 'TAB';
                                 array_push($default_offers, $offers_data);
    
                                 $offers_data = new Offer();
                                 $offers_data->title = 'Trajects facturé a au KM Depuis '.$departing.' jusque á '.$arrival;
                                 $offers_data->description  = 'Trajects facturé a lá Minute Depuis '.$departing.' jusque á '.$arrival;
                                 $offers_data->departing = $departing;
                                 $offers_data->dlat = NULL;
                                 $offers_data->dlng = NULL;
                                 $offers_data->radiusd= NULL;
                                 $offers_data->arrival = $arrival;;
                                 $offers_data->alat = NULL;
                                 $offers_data->dlng =  NULL;
                                 $offers_data->trip_method = 'TAB';
                                 array_push($default_offers, $offers_data);
    
                                 return $default_offers;
    
                       }
    
                function default_offers($drivers, $offers_existing)
                     {
    
    
                         $cars = [];
    
                         foreach($drivers as $driver)
                         {
                           if($driver->car != NULL)
                           {
                             foreach($driver->car as $car)
                             {
                                 $found = FALSE;
    
    
                                 foreach($cars as $saved_car)
                                 {
                                     if($saved_car->id == $car->id)
                                         $found = TRUE;
                                 } 
    
                                 if($found == FALSE)   
                                     array_push($cars, $car);
                             }
                           }
    
                         }
    
    
    
    
    
                        if(count($drivers))
                        {     
    
    
    
                             $Offer = new Offer;
                             $Offer->id = -1;
                             $Offer->title ="Tarif par KMS dans la region ".$this->departing;
                             $Offer->description ="Tarif par KMS dans la region ".$this->departing;
                             $Offer->departing = $this->departing;
                             $Offer->dlat = $this->dlat;
                             $Offer->dlng = $this->dlng; 
                             $Offer->radiusd = 0;
                             $Offer->calc_method = 1;
                             $Offer->cost = $this->total_trip_distance * $this->km_unit;
                             $Offer->kms = round(floatval($this->total_trip_distance),2);
                             $Offer->unit = $this->km_unit;
                             $Offer->cars =  $cars;
                             $Offer->trip_method = 'TZG';
                             $Offer->unit = $this->kms;
                             array_push($offers_existing, $Offer);
    
                             $Offer = new Offer;
                             $Offer->id = -1;
                             $Offer->title ="Tarif à la minute dans la region ".$this->departing;
                             $Offer->description ="Tarif à la minute dans la region ".$this->departing;
                             $Offer->departing = $this->departing;
                             $Offer->dlat = $this->dlat;
                             $Offer->dlng = $this->dlng; 
                             $Offer->radiusd = 0;
                             $Offer->calc_method = 2;
                             $Offer->kms = round(floatval($this->total_trip_distance),2);
    
    
                             //calc get by google gets the distance and time
                             //we know distance and time
                             //ex: 20 KM in 25 MINUTES
                             // 20 KM / 25 Min == esch minutes as amout of KM
                             // 1 Min = 0.8 KM
                             // 1 KM = pirce ex: 2 € $this->KMS_unit
                             // 2 * 0.8 = price / minute, cause 1 Km = 2 € and 0.8 km = Minute so =0.8 KM  * 2  price per minute
    
                             $KMS_per_minute = $this->total_trip_distance/ $this->total_trip_time;
                             $price_perminute = $this->km_unit *  $KMS_per_minute;
                             $Offer->cost =   round($price_perminute * $this->total_trip_time,2);
                             $Offer->minutes =   round(floatval($this->total_trip_time),2);
                             $Offer->unit =$this->minute_unit;
                             $Offer->cars=  $cars;
                             $Offer->trip_method = 'TZG';
                             array_push($offers_existing, $Offer);
    
    
                             if($this->trip_method == 'TAB')
                             {
                                 $Offer = new Offer;
                                 $Offer->id = -1;
                                 $Offer->title ="Tarif à Fixe depuis ".$this->departing.' jusque à '.$this->arrival;
                                 $Offer->description ="Tarif à Fixe depuis ".$this->departing.' jusque à '.$this->arrival;
                                 $Offer->departing = $this->departing;
                                 $Offer->dlat = $this->dlat;
                                 $Offer->dlng = $this->dlng; 
                                 $Offer->arrival = $this->arrival;
                                 $Offer->alat = $this->alat;
                                 $Offer->alng = $this->alng; 
                                 $Offer->radiusd = 0;
                                 $Offer->calc_method = 1;
                                 $Offer->cost = $this->total_trip_distance * $this->km_unit;
                                 $Offer->kms = round(floatval($this->total_trip_distance),2);
                                 $Offer->unit = $this->km_unit;
                                 $Offer->cars =  $cars;
                                 $Offer->trip_method = $this->trip_method;
                                 array_push($offers_existing, $Offer);
                             }
                        }
    
                         return $offers_existing;
    
                     }
    
                     function get_best_driver_all($drivers)
                     {
                           $fastest_driver = NULL;
                           foreach($drivers as $driver)
                           {
                               if($driver->availability == 'ALL')
                               {
                                   if($fastest_driver == NULL)
                                        $fastest_driver = $driver;
    
                                        if($driver->total_time_plus <  $fastest_driver->total_time_plus)
                                         $fastest_driver = $driver;
                               }
                           }
    
                           return $fastest_driver;
    
                        }
    
    
    
    
                   function timesdashboard($id){
                            $driver = Driver::find($id); 
                            return View::make('drivers.times.index',['driver' => $driver,'load_time_table' => 'load_time_table('.$driver->user_id.')']);
                   }
    
    
    
    
                 function timesheet($id)
                  {
    
                            $times = User::find($id)->times;
    
                            $timesheet =[];
    
                            for($i=0; $i<24; $i++)
                            {
                                $timeline = new Timeline($i);
                                array_push($timesheet, $timeline);
                            }
    
    
                          foreach($times as $time)
                          {
                                //echo "start-".$time->start;
                                //echo " end-".$time->end;
                            $start =  intval(substr($time->start,0,2));
                            $end =  intval(substr($time->end,0,2));
    
                            $day = $time->day_week;
                            $paint = FALSE;
    
                            foreach($timesheet as $timeline)
                            {   
                                //if interval start set painting to true
                                 if(intval($timeline->hour) == intval($start))
                                    $paint = TRUE;
                                //if interval stops set painting to false
                                 if(intval($timeline->hour) == intval($end))
                                     $paint = FALSE;
                                //if paint dset DAY-HOUR to painted or active
                                 if($paint == TRUE)
                                    $timeline->$day= 'active';
    
                                 //if the last item i setted to active i set to tru because we dont have a 24 to set up the stop
                                if(intval($timeline->hour) == 23 and intval($end)==23)
                                    $timeline->$day= 'active';
    
                             } 
                          }
    
                             $this->Jtable($timesheet);
                        }
                 }
    
    
    
     class Availability
     {
         public $offers=[];
         public $drivers=[];
    
     }
