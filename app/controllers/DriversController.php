<?php
    
            class DriversController extends BaseController {
    
                public function __construct()
                {
                    //$this->beforeFilter('auth.company',  array('only' => array('index') ));
                }
    
                public function dashboard()
                {
                  return View::make('drivers.index');
                }
    
                public function index()
                {  
                    $drivers =  Driver::with('user')->get();
                    $this->Jtable($drivers);
                }
    
    
                function oauth2callback()
                {
    
                    echo ' {"web":{"auth_uri":"https://accounts.google.com/o/oauth2/auth","client_secret":"cVFJdpmg7vCarDUeDnFfh9ZE","token_uri":"https://accounts.google.com/o/oauth2/token","client_email":"656391148071-a2ai7qrb7v0jb1gq09r1alsd1gul2pno@developer.gserviceaccount.com","redirect_uris":["http://localhost:8080/oauth2callback"],"client_x509_cert_url":"https://www.googleapis.com/robot/v1/metadata/x509/656391148071-a2ai7qrb7v0jb1gq09r1alsd1gul2pno@developer.gserviceaccount.com","client_id":"656391148071-a2ai7qrb7v0jb1gq09r1alsd1gul2pno.apps.googleusercontent.com","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","javascript_origins":["http://localhost:8080/"]}}';
                }
    
    
                function get_fastest_route($arr_routes)
                {
                    $fastest_route= NULL;
                    $min_duration =NULL;
                    foreach($arr_routes as $a_route)
                    {
                         $duration = $a_route->legs[0]->duration->value;
    
                        if($min_duration == NULL)
                           $min_duration = $duration;
    
                       if($duration < $min_duration)
                            $min_duration = $duration;
    
                    }
    
                   foreach($arr_routes as $route)
                   {
    
                       if($route->legs[0]->duration->value == $min_duration)
                       {
                            $fastest_route = $route;
                            break;
                       }
                   }
    
                   return  $fastest_route;
    
                }
    
                function driver_trip_find()
                {   
                 
                   //define a var to bind the drivers available for the course adn the approved aone, the one that capacle to arrive at time
                   $drivers_availbale_for_the_course = [];
                   $approved_drivers =[];


                   $result = NULL;
                    //where we receive the trip parameter 
                   $departing = $_GET["departing"];
                   $arrival = $_GET["arrival"];
                   //where we receive the departing time so is time and we get the day of week to find in the agenda table of each driver is is working or not
                   $departing_time  =  $_GET["time"];
                   $trip_duration = 0;
                   $trip_distance = 0;
                   $day=  date('D', strtotime($departing_time));
    
                   //estimated arrival by usng google maps
                   $arrival_time = NULL; 
    
                    //echo 'Departing: '.$departing;
                    //echo '</br>';
                    //echo 'Arrival: '.$arrival;
                    //echo '</br>';
                    //echo 'Departing time : '.$departing_time.' ,'.$day;
    
              //      //to find in the agenda table we have to know the arrival_time of the trip,
                    //because we just have the departing time we have to calc the duration of the trip by using geocode and google directions api.
    
                    $param = array("origin"=> $departing, "destination" => $arrival);
                    $reponse = Geocoder::directions('json', $param);
    
                    if($reponse != NULL)
                        $result = json_decode($reponse);
    
                    if($result != NULL)
                    {
    
    
                             //foreach($result->routes as $route)
                             //{
                             //    echo $route->legs[0]->duration->value.' Minutess'. floatval( $route->legs[0]->duration->value / 60 );
                             //   
                             //    echo '</br>';
                             //}   
    
                            $route = $this->get_fastest_route($result->routes);
    
                            $trip_duration = $route->legs[0]->duration->value;
                            $trip_distance = $route->legs[0]->distance->value;
    
    
                            $arrival_time =date('Y-m-d H:i:s',strtotime($departing_time) + $trip_duration);
                            //echo '</br> Arrival time : '.$arrival_time.' ,'.$day;
                          
                            $start_hour = date('H',strtotime($departing_time)).':00';
                            $end_hour =  date('H',strtotime($departing_time) + $trip_duration).':00';
    
    
    
    
                            $sql ='select drivers.id, drivers.user_id, drivers.company_id, drivers.avatar_url, drivers.created_at,
                                        drivers.updated_at  ,users_times.start, users_times.end from drivers 
                                        inner join users_times on (drivers.user_id = users_times.user_id)
                                        where (users_times.start<= "'.$start_hour.'" and users_times.`end` >= "'.$end_hour.'" and users_times.`day_week` ="'.$day.'") and drivers.id NOT IN(select bookings.driver_id  from bookings
                                        where
                                        (bookings.driver_arrival_time between "'.$departing_time .'" and "'.$arrival_time.'" ) or
                                        (bookings.arrival_date between "'.$departing_time .'" and  "'.$arrival_time.'") or
                                        (bookings.driver_arrival_time <= "'.$departing_time .'" and bookings.arrival_date >= "'.$arrival_time.'") group by driver_id)';
    
    
    
    
                          $drivers = DB::select($sql);
    
                         $drivers_availbale_for_the_course = [];
    
                        foreach($drivers as $driver)
                        {
    
                            $db_driver = Driver::find($driver->id);
                            if($db_driver  != NULL)
                            {
                                $db_driver->start  = $driver->start;
                                $db_driver->end  = $driver->end;
                                //   echo '</br>';
                                //echo json_encode($db_driver);
                                //echo '</br>';
                                array_push($drivers_availbale_for_the_course, $db_driver );
                            } 
                        }
                    }
    
    
                    $today =  date('Y-m-d', strtotime($departing_time));
    
    
    
                    $drivers_prev_next = [];
    
    
    
                    foreach($drivers_availbale_for_the_course as $driver)
                    { 
    
                        //we need the default driver point when drivers has no trips to do, tis means that driver should be on the garage (company)
                       $prev = new MapPoint(49.599224, 6.133164999999963, 'Luxembourg Gare Centrale quai 13, Luxemburgo', $driver->start);
                       $next = new MapPoint(49.599224, 6.133164999999963, 'Luxembourg Gare Centrale quai 13, Luxemburgo', $driver->end);
                       //echo '</br>';
                       //echo $driver->id;
    
    
                       $booking_before = Booking::where('driver_id','=',$driver->id)
                       ->where('arrival_date','>=',  $today) // all bookings where arrival is to
                       ->where('arrival_date','<', $departing_time)
                       ->orderBy('arrival_date', 'desc')->first();
    
    
                      $booking_after = Booking::where('driver_id','=',$driver->id)
                      ->where('driver_arrival_time','>=',  $today)
                      ->where('driver_arrival_time','>', $arrival_time)
                      ->where('driver_arrival_time','>=',  $today)->orderBy('driver_arrival_time', 'asc')->first();
    
    
    
                     //if user has not a booking after for today that means that he is one the garage
                     if(count($booking_before) ==1)
                       $prev = new MapPoint($booking_before->arrival_point_lat, $booking_before->arrival_point_lng, $booking_before->arrival_address, $booking_before->arrival_date);
    
                     if(count($booking_after)==1)
                        $next = new MapPoint($booking_after->departing_point_lat, $booking_after->departing_point_lng, $booking_after->departing_address, $booking_after->driver_arrival_time);
    
    
                        $driver_point = new DriverPoint($driver->id, $prev, $next);
    
                        array_push($drivers_prev_next, $driver_point);
                    }   
    
     // echo '</br>';
    //
     //// echo $sql;
     //   echo '</br>';
    
                 //now for each driver we have is last position and next position
                 //to calculate the driver tha is more close for the new trip we have to think
                 //so
                 // all this drivers are been picked beacuse they got an interval in they agenda to do the job
                 //but doees not mean that we can do it, because depende on distance that the driver is from the job
                 //if is to much far it wil possible that we cannot arrive at time to do the job because we sent the tim,e detect for foind the couse in the trip to arrive to job
                 //but must important is the prev position, that is the positoin where the driver was when finnishid the last trip
                 // and next positonwhere driver will be next
                 //if they have no trip, last and next will be the garage or park place
                 // Calculatio is made by evaluating the distance , or time where the taxi is ffrom the new job
                 //of couse we have to take the one thta is more close
                 //but not ever the one that is more close is the best one
                 //but also the anos that have jobs after of before where the routes are passing trhougth new point
                 //Imagine you have a TRIP from PARIS - Crawley, West Sussex UK 10:30 - 14:30
                 // DRIVER 1 100 KM from PARIS at SOUTH
                 // DRIVER 2 200 KM from PARIS at SOUTH
                 // WILL THINK DRIVER 1 is the most close in time and distance
    
                 //BUT DRIVER 2 was job at 19:00 from    LONDON -> OXFORD the values is just for perpective, couse no nobody will take a driver from france to make a trip ion UK
                 //but what is small is in big
                 //so if you make calculations to make the new job for driver 1 that will be imagining that central of company is in paris
                 // 100 KM to PARIS + 452 KM to Crawley, West Sussex + 462 KM to came back to company Paris =>> 924 KM , we have to do 904 Km to do this job, because 100 KM is for going to  company
                 //and we have always to return to company at end of the day, so if we has at 100 km from paris we have to 100 km
                 //if you look at driver 2 one thing is sure we must to PARIS TO LONDON to make the TRIP LONDON -> OXFORD
                 //200 KM to PARIS + 452 KM to London  that is 652 KM -> that we have to do
    
                //but if accept the job PARIS Crawley WE WILL DO
                // 200 to paris + 462 to Crawley + 50 km to london = 712 to arrive to OXFORD or next
                // when you compare driver 1 that is at 100 KM from paris and Driver 2 that is at 200
                // the KM spend to the trip are to much less for the driver 2, cause is to the direction of the current job
                // before is was doint 652 KM to do is job, if we accept will do 712, more 60 KM
                // thats the diference from prev to next driver 1   is 100 KM to garage if accept we have to to do more 824 KM to do this job
                // driver from prev 200 Km south  to next to London is 652
                // if accept it will be 200 + 462 + 50  will do just 60 KM more or 764  less that driver 1 beacause is goind in the direction to of the current job
    
                   // will store ID's from drivers that can do the courses
                 
                   foreach($drivers_prev_next as $driver_pos)
                   {
                        //echo "Making calc for driver" + $driver_pos->id;
                        $param = array("origin"=> $driver_pos->prev->location, "destination" => $driver_pos->next->location);
                        $reponse = Geocoder::directions('json', $param);
    
    
                        $default_trip_time =0;
                        $default_trip_distance =0;
    
    
                        $distance_to_trip =0;
                        $duration_to_trip =0;
    
                        $distance_to_next =0;
                        $duration_to_next =0;
    
                        $driver_can_do = TRUE;
    
                        if($reponse != NULL)
                        {
                            $result = json_decode($reponse);
    
                            if($result != NULL)
                            {
                             //  echo '</br>';
                               $route = $this->get_fastest_route($result->routes);
                           
                               $default_trip_time = $route->legs[0]->duration->value;
                                 
                               $default_trip_distance= $route->legs[0]->distance->value;
                            //   echo 'FROM '.$driver_pos->prev->location.' TO'.$driver_pos->next->location.' is: '.$route->legs[0]->distance->text.' and takes : '.floatval($default_trip_time/60).' minutes';
    
    
    
                              $param = array("origin"=> $driver_pos->prev->location, "destination" => $departing);
                              $reponse = Geocoder::directions('json', $param);
    
                               if($reponse != NULL)
                               {
                                    $result = json_decode($reponse);
    
                                    if($result != NULL)
                                    {
                                        $route = $this->get_fastest_route($result->routes);
                                     //   echo '</br>';
                                        $duration_to_trip = $route->legs[0]->duration->value;
                                        $distance_to_trip= $route->legs[0]->distance->value;
    
                                         $driver_time_arrival =  date('Y-m-d H:i:s',strtotime($driver_pos->prev->time) + $duration_to_trip);
    
                                        //echo 'FROM DRIVER POS '.$driver_pos->prev->location.' AT '.$driver_pos->prev->time.' TO TRIP => '.$departing.' is: '.$route->legs[0]->distance->text.' and takes : '.floatval($duration_to_trip/60).' minutes , driver will arrive at '.$driver_time_arrival;
    
  
                                         if(strtotime($driver_time_arrival) > strtotime($departing_time))
                                           $driver_can_do = FALSE;
                                         else
                                           $driver_can_do = TRUE;
    
    
    
                                         if($driver_can_do)
                                         {
    
                                             $param = array("origin"=>$arrival, "destination" => $driver_pos->next->location);
                                             $reponse = Geocoder::directions('json', $param);
    
                                             if($reponse != NULL)
                                             {
                                                $result = json_decode($reponse);
    
                                                if($result != NULL)
                                                {
                                                    $route = $this->get_fastest_route($result->routes);
                                                    //echo '</br>';
                                                    $duration_to_next  = $route->legs[0]->duration->value;
                                                    $distance_to_next= $route->legs[0]->distance->value;
    
                                                    $driver_time_arrival =  date('Y-m-d H:i:s',strtotime($arrival_time) + $duration_to_next);
    
    
                                                    //echo "TRIP ARRIVAL->'.$arrival_time.' -> DRIVER ARRIVAL TIME ".$driver_time_arrival.' '.$route->legs[0]->duration->value.' seconds plus </br>';
                                                    //echo 'FROM TRIP '.$arrival.' TO NEXT DRIVER POS => '.$driver_pos->next->location.' SHOULD ARRIVE AT '.$driver_pos->next->time.' and  is: '.$route->legs[0]->distance->text.' and takes : '.floatval($duration_to_next/60).' minutes';
    
    
                                                  if(strtotime($driver_time_arrival) <= strtotime($driver_pos->next->time))
                                                  { 
                                                      $total_time_spent= $duration_to_trip + $duration_to_next + $default_trip_time;
                                                      //echo '</br>';
    
                                                      //echo 'Distance to NEXT: '.$distance_to_next; echo '</br>';
                                                      //echo  'TRIP DIstance'.$trip_distance;
                                                      $total_distance = $distance_to_trip + $distance_to_next + $trip_distance;
                                                      //echo 'Distance to trip: '.$distance_to_trip; echo '</br>';
                                                      //echo "</br> TOTAL TIME SPENT TO MAKE THE NEW TRIP: ".floatval($total_time_spent/60);
                                                      //echo "</br> TOTAL DISTANCE SPENT TO MAKE THE NEW TRIP: ".floatval( $total_distance/1000);
                                                      //echo '</br>ORIGINAL TRIP DISTANCE : '.$default_trip_distance;
                                                      //echo '</br>NEW TRIP DISTANCE: '.$total_distance;
                                                      //echo "</br> TOTAL DISTANCE PLUS: ".($total_distance -$default_trip_distance)/1000;
                                                      
                                                      $driver_pos->original_time = $default_trip_time;
                                                      $driver_pos->new_trip_time = $total_time_spent;
                                                      $driver_pos->total_time_plus = ($total_time_spent - $default_trip_time);

                                                      $driver_pos->original_distance = $default_trip_distance;
                                                      $driver_pos->new_trip_distance = $total_distance;
                                                      $driver_pos->total_distance_plus = ($total_distance - $default_trip_distance);
                                                      array_push($approved_drivers, $driver_pos);
                                                  }
    
                                                }
    
                                           }
    
                                        }
    
                                    }
    
                               }
                            }
    
                       }
                        //echo '</br>';
                   }

                   echo json_encode($approved_drivers);
                    //echo json_encode($drivers_availbale_for_the_course);
                }
    
                function availablibility()
                {
    
                   $start  =  $_GET["departing_date"];
                   $end  =  $_GET["arrival_date"];
    
                   $day=  date('D', strtotime($start));
    
    
                   $start_date = new DateTime($start);
                   $start_time =   $start_date->format('H:i:s');
    
                   $end_date = new DateTime($end);
                   $end_time =  $end_date->format('H:i:s');
    
             //      //this retuns all drivers with  working on this day
                   //$users = User::with('driver')->whereHas('times', function($q) use ($day, $start_time, $end_time) 
                   // { 
                   //    $q->where('day_week', $day)->where('day_week', $day)->where('start','<=',$start_time)->where('end','>=',$end_time);
                   // })->whereHas('driver', function($q)
                   // {
    
    
        //
             //      // })->get();
    
    
    
    
                $sql ='select drivers.id, drivers.user_id, drivers.company_id, drivers.avatar_url, drivers.created_at,
                                        drivers.updated_at  ,users_times.start, users_times.end from drivers 
                                        inner join users_times on (drivers.user_id = users_times.user_id)
                                        where (users_times.start<= "'.$start_time.'" and users_times.`end` >= "'.$end_time.'" and users_times.`day_week` ="'.$day.'") and drivers.id NOT IN(select bookings.driver_id  from bookings
                                        where
                                        (bookings.driver_arrival_time between "'.$start.'" and "'.$end.'" ) or
                                        (bookings.arrival_date between "'.$start.'" and  "'.$end.'") or
                                        (bookings.driver_arrival_time <= "'.$start.'" and bookings.arrival_date >= "'.$end.'") group by driver_id)';
        //  echo "<br/>";
        //echo $sql;
        //  echo "<br/>";
    
                     $drivers = DB::select($sql);
    
    
                    //next wee need to find each one as time to do the course
                    // we consider departing time - 15 min to
                    // will look just for drivers that have nothing to do in 15 min before the trip to consider the time that driver takes to arrive
                    // however this is not enougth
                    //but t eminimum to consider is 15  before, is there is more will no be aproblem course
    
    
    
                    $drivers_availbale_for_the_course = [];
    
                    foreach($drivers as $driver)
                    {
    
    
                        $db_driver = Driver::with('bookings')->with('user')->find($driver->id);
                        if($db_driver  != NULL)
                        {
                            $db_driver->start  = $driver->start;
                            $db_driver->end  = $driver->end;
                            array_push($drivers_availbale_for_the_course, $db_driver );
                        } 
                    }
    
                    echo json_encode($drivers_availbale_for_the_course);
                }
    
                public function timesdashboard($id){
                    $driver = Driver::find($id); 
                    return View::make('drivers.times.index',['driver' => $driver,'load_time_table' => 'load_time_table('.$driver->user_id.')']);
                }
    
                public function timesheet_save($id)
                {
                    $result = 1;
    
                    $intervals = array();
    
                    $user_time=  UserTimes::where('user_id',$id)->orderBy('id','desc')->get()->first();
    
                    if(isset($_GET["data"]))
                        $intervals = $_GET["data"];
    
                    foreach($intervals as $interval)
                    {
                      $user_times = new UserTimes;
                      $user_times->fill($interval);
                      $result =  $user_times->save();
                      if($result== 0)
                      {
                          UserTimes::where('user_id',$id)->where('id','>',  $user_time->id)->delete();
                        return 0;
                      }
                    }
    
                    if($result==1 && isset($user_time))
                    {
                         UserTimes::where('user_id',$id)->where('id','<=',  $user_time->id)->delete();
                    }
                }
    
                 public function timesheet($id)
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
