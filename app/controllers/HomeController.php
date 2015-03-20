<?php
    class HomeController extends BaseController {
        /*
        |--------------------------------------------------------------------------
        | Default Home Controller
        |--------------------------------------------------------------------------
        |
        | You may wish to use controllers instead of, or in addition to, Closure
        | based routes. That's great! Here is an example controller method to
        | get you started. To route to this controller, just add the route:
        |
        |	Route::get('/', 'HomeController@showWelcome');
        |
        */
        public function index()
        {   
            $user= new User;
    
            if(count(Auth::user()) > 0)
                $user = Auth::user();
    
            return View::make('frontend.'.$this->company->theme.'.index', ['company' => $this->company, 'user' => $user]);
    
    
        }
        public function admin()
        {    
    
            if(count(Auth::user()) > 0)
            {  
    
                 return View::make('backoffice.dashboard', ['user' => Auth::user()]);
            }
        }
    
        public function myprofile()
        {   
            $companies = NULL;
            $mode ="edit";
            if(Auth::user()->is_super_admin())
                  $companies = Company::where("active", "=", "1")->get();
            $workflow = 'users';
            $cars =[];
             if(Auth::user()->is_driver())
             {
                $driver_id =Auth::user()->driver->id;
                $sql= "select *, CONCAT(cars.model, cars.brand) as FullName from cars inner join drivers_cars on (cars.id = drivers_cars.car_id) where drivers_cars.driver_id = ".$driver_id;
                
                
                $cars = DB::select($sql);
             }


            return View::make('users.user.index', ['user' => Auth::user(), 'mode' => $mode, 'companies' => $companies, 'auth' => Auth::user(), 'workflow' => $workflow, 'cars' => $cars]);
        }
    }
