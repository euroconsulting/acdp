<?php
    
        class OffersController extends BaseController {
    
            public function index()
            {
                $this->JTable(Offer::all());
            }
    
            public function dashboard()
            {
                return View::make('offers.index');
            }
    
    
            public function edit($id)
            {
                $offer = new Offer;
                $mode = "new";
    
                if($id > 0)
                {
                    $offer = Offer::find($id);
                    $mode = "edit";
                }
    
               $companies = NULL;
               $company_id = $this->company->id;
               $drivers =   Driver::with('user')->whereHas('user', function($q) use ( $company_id)
                                {
                                    $q->where('company_id', '=', $company_id);

                                })->where('active','=', true)->get();


               $cars =  Car::where("company_id", "=", $this->company->id)->get();
               if(Auth::user()->is_super_admin())
               {
                  $companies = Company::where("active", "=", "1")->get();
               
               }
                
                return View::make('offers.offer.index',['mode' => $mode, 'offer' => $offer, 'drivers' =>  $drivers, 'cars' => $cars, 'companies' => $companies, 'auth' => Auth::user()]);
            } 
    
            public function save()
            {
    
                $status = new Status(TRUE, 'success', 'saved');
    
                $offer = NULL;
    
                $mode = NULL;
                $offer_data = [];
                $drivers_data  =[];
                $cars_data = [];
                $timesheet_data = [];
    
    
    
                 if(isset($_POST["offer"]))
                    $offer_data = json_decode($_POST["offer"]);
    
                 if(isset($_POST["drivers"]))
                    $drivers_data = json_decode($_POST["drivers"]);
    
                 if(isset($_POST["cars"]))
                    $cars_data = json_decode($_POST["cars"]);
    
                  if(isset($_POST["timesheet"]))
                    $timesheet_data = json_decode($_POST["timesheet"]);
   
  
                try
                {
   
                    if($offer_data->id > 0)
                        $offer = Offer::find($offer_data->id);
                    else
                        $offer = new Offer;
     
                    $offer->fill((array)$offer_data);
 
                    if(Auth::user()->is_super_admin())
                        $offer->company_id = $offer_data->company_id;

                   
                    $offer->save();
    
                }
                catch(Exception  $ex)
                {
                    $status->result = FALSE;
                    $status->status='error';
                    $status->msg = $ex;
               
                }

                if($offer != NULL)
                {
                    try
                    {
                        //save drivers
                        foreach($drivers_data as $driver_data)
                        {
                            //check if driver already exists
                            if(!$offer->CheckDriver($driver_data->driver_id))
                            {
                                $driver = new OfferDriver;
                                $driver->offer_id = $offer->id;
                                $driver->fill((array)$driver_data);
                                $offer->Drivers()->save($driver);
                            }
                        }
    
                        $drivers = $offer->Drivers()->get();
                        foreach($drivers as $driver)
                        {   
                            $found = FALSE;
                            foreach($drivers_data as $driver_data)
                            {
                               if($driver->driver_id == $driver_data->driver_id)
                                  $found = TRUE;
                            }
    
                            if(!$found)
                            {
    
                              OfferDriver::where("offer_id", "=", $offer->id)->where("driver_id", "=", $driver->driver_id)->delete();
                            }
    
                      }
    
                       //save cars
                       foreach($cars_data as $car_data)
                       {
    
    
                            if(!$offer->CheckCar($car_data->car_id))
                            {
    
                                $car = new OfferCar;
                                $car->car_id =  $car_data->car_id;
                                $offer->Cars()->save($car);
                            }
    
                        }
    
    
                        $cars = $offer->Cars()->get();
                        foreach($cars as $car)
                        {   
                            $found = FALSE;
                            foreach($cars_data as $car_data)
                            {
                               if($car->car_id == $car_data->car_id)
                                  $found = TRUE;
                            }
    
                             if(!$found)
                            {
                                OfferCar::where("offer_id", "=", $offer->id)->where("car_id", "=", $car->car_id)->delete();
                            }
    
                        }
    
   
                    //save timesheet
                    $this->timesheet_save($offer->id, $timesheet_data);
    
                    //save the image
                        if (Input::hasFile('img'))
                        {
                            $file = Input::file('file');
                         
                            $destinationPath ='img/offers/';
                            $db_path= 'img/offers/'.$offer->id.'.png';
                            // If the uploads fail due to file system, you can try doing public_path().'/uploads' 
    
                            $filename = $offer->id.'.png';
                            $upload_success = Input::file('img')->move($destinationPath, $filename);
    
                            $offer->img = $db_path;
                            $offer->save();

                        }
    
                    
                   }
                    catch(Exception  $ex)
                    {
                        $status->result = FALSE;
                        $status->status='error';
                        $status->msg =$ex;
                         echo $ex;
                    }
                }
    
               return json_encode($status);
            }
    

            function timesheet_save($id, $intervals)
            {
                $result = FALSE;

                $offer_time_last=  OfferTimes::where('offer_id','=', $id)->orderBy('id','desc')->get()->first();


                foreach($intervals as $interval)
                {
                    $offer_time = new OfferTimes;
                    $offer_time->fill((array)$interval);
                    $offer_time->offer_id = $id;
                    $result = $offer_time->save();
                    if(!$result)
                    {
                       OfferTimes::where('offer_id','=', $offer_time_last->id)->where('id','>',  $offer_time_last->id)->delete();
                        return FALSE;
                    }
                }
    
                if($result && count($offer_time_last))
                {
                     OfferTimes::where('offer_id','=', $offer_time_last->offer_id)->where('id','<=',  $offer_time_last->id)->delete();
                }

                return $result;
                           
            }


            public function by_id($id)
            {
              echo "id: ".$id;
    
            }
    
    
            public function timesheet($id)
            {
    
                $offer = Offer::find($id);
    
                $timesheet =[];
    
                for($i=0; $i<24; $i++)
                {
                    $timeline = new Timeline($i);
                    array_push($timesheet, $timeline);
                }
    
                if(isset($offer))
                {
    
                    foreach($offer->TimeSheet as $time)
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
                }
    
                $this->Jtable($timesheet);
            }
    
    }
