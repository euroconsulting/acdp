<?php
    
      class CarsController extends BaseController {
    
    
          public function dashboard()
          {
              return View::make('cars.index');
          }
    
          public function index()
          {  
              if(Auth::user()->is_super_admin())
                $cars =  Car::get();
              else
                $cars =  Car::where("company_id", "=", $this->company->id)->get();
           
              $this->Jtable($cars);
          }
    
          public function save()
          {  

              $status = new Status(TRUE, 'error', 'An error ocurred when saving the car data');
    
    
              if(isset($_POST["car"]))
              {
    
    
                 $car_data = json_decode($_POST["car"]);
    
                 try  
                 {
                      if($car_data->id > 0)
                      { 
                          $car = Car::find($car_data->id);
                          $car->fill((array)$car_data);
                      }
                      else
                      {
                           $car = new Car;
                           $car->fill((array)$car_data);
                      }
                      if(Auth::user()->is_super_admin())
                      {
                          $car->company_id = $car_data->company_id;
                      }
                      else
                      {
                           $car->company_id  = $this->company->id;

                      }
                  
    
                  $car->save();
    
                  if (Input::hasFile('img'))
                  {
                      $file = Input::file('file');
            
                      
                      $destinationPath ='img/cars/';
                      $db_path= 'img/cars/'.$car->id.'.png';

                      // If the uploads fail due to file system, you can try doing public_path().'/uploads' 
    
                      $filename = $car->id.'.png';
                      $upload_success = Input::file('img')->move($destinationPath, $filename);
    
                      $car->img = $db_path;
                      $car->save();

                  }

                  $status->status='success';
                  $status->msg = 'The car as been sucessfull saved';
                 }
                 catch(Exception $ex)
                 {    
                      $status->result=FALSE;
                      $status->status='error';
                      $status->msg =  'An error ocurred when saving the the car , please try again later';
                      echo $ex;
                 }
    
                echo json_encode($status);
    
              }
    
          }
          public function car($id)
          {  
               return View::make('cars.car.index');
          }
    
          public function edit($id)
          {   $car = NULL;
    
    
              if($id > 0)
              {
                  $car = Car::find($id);
                  $mode ='edit';
              }else
              {
                  $mode ='new';
                  $car = new Car;
              }
    
              $companies = NULL;
    
              if(Auth::user()->is_super_admin())
              {
                  $companies = Company::where("active", "=", "1")->get();
              }

              return View::make('cars.car.index', ['car' => $car, 'mode' => $mode, 'companies' => $companies, 'auth' => Auth::User()]);
          }
      }
