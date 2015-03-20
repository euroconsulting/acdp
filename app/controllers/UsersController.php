<?php
    class UsersController extends BaseController {
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
    
            if(Auth::User()->is_super_admin())
                $users = User::all();
            else
                $users = User::where("company_id", "=", $this->company->id)->get();
    
            $this->Jtable($users); 
        }
    
    
        public function dashboard()
        {
            return View::make('users/index');
        }
    
    
    
        public function edit($id)
        {   
            $workflow= 'users';
            if(isset($_GET["flow"]))
                $workflow = $_GET["flow"];
    
            $user = NULL;
    
              if($id > 0)
              {
                  $user = User::find($id);
                  $mode ='edit';
              }else
              {
                  $mode ='new';
                  $user = new User;
              }
    
              $companies = NULL;
    
    
              if(Auth::user()->is_super_admin())
                    $companies = Company::where("active", "=", "1")->get();
    
              $cars = Car::where('company_id','=', $user->company_id)->get();
    
    
              return View::make('users.user.index', ['user' => $user, 'mode' => $mode, 'companies' => $companies, 'auth' => Auth::user(), 'cars' =>  $cars, 'workflow' => $workflow]);
       }
    
    
    
        public function save()
        {   
    
            $status = new Status(TRUE, 'success', 'saved');
    
            $mode = NULL;
            $user_data = NULL;
            $customer_data= NULL;
            $driver_data = NULL;
    
            //inicalize user to use later
            $user = NULL;
    
    
    
    
            if(isset($_POST["user"]))
                $user_data = json_decode($_POST["user"]);
    
    
            //get customer flag
            if(isset($_POST["customer"]))
                $customer_data = json_decode($_POST["customer"]);
    
             //get driver flag
            if(isset($_POST["driver"]))
                $driver_data =  json_decode($_POST["driver"]);
    
    
    
            if($user_data->id > 0)
            {
                $user = User::find($user_data->id);
            
            }
            else
                $user = new User; 
    
                $user->fill((array)$user_data);
                if($user_data->password != NULL)
                        $user->password = Hash::make($user_data->password);
    
            try
            {
               
    
    
                
                $user->save();
                $status->msg = 'User '.$user->Fullname.' saved'; 
            }
            catch(Exception  $ex)
            {
                $status->result = FALSE;
                $status->status='error';
                $status->msg = $ex;
            }
    
    
            if(Auth::user()->admin)
            {
                 //update customer
                try
                {
    
                    $customer = Customer::where("user_id","=",$user->id)->first();
    
                    if($customer != NULL)
                        $customer->active=  $customer_data;
                    else
                    {
                            $customer = new Customer;
                            $customer->user_id = $user->id;
                            $customer->active = $customer_data;
                    }
    
                    if($customer != NULL && isset($customer_data))
                        $user->Customer()->save($customer);
    
    
                    $driver = Driver::where("user_id","=",$user->id)->first();
    
                    if($driver != NULL)
                    {
    
                        $driver->fill((array)$driver_data);
                        $driver->user_id = $user->id;
                        $user->Driver()->save($driver);
                    }
                    else
                    {
                        if($driver_data->active)
                        {
                            $driver = new Driver;
                           
                            $driver->fill((array)$driver_data);
                            $user->Driver()->save($driver);
                        }
                    }
    
                    if(count($driver))
                    {
                        if(isset($_POST['cars']))
                        {
                            $cars = json_decode($_POST['cars']);
    
                            DriverCars::where("driver_id","=", $driver->id)->delete();
    
                            foreach($cars as $driver_car_data)
                            {   
    
                               $driver_car = new DriverCars;
                               $driver_car->car_id = $driver_car_data->car_id;
                               $driver_car->driver_id = $driver->id;
                               $driver_car->save();
    
    
                            }
    
    
                        }
    
    
                    }
    
    
                }
                catch(Exception $ex)
                {
                    $status->result = FALSE;
                    $status->status='warning';
                    $status->msg = $ex;
    
                }
    
            }
    
    
    
            //Save the image
    
             if (Input::hasFile('img'))
             {
                      $file = Input::file('file');
                    
                      $root = $user->Company->root_path.'/'; 
             
                      $destinationPath ='img/users/';
             
                      $db_path= 'img/users/'.$user->id.'.png';
                      
                      // If the uploads fail due to file system, you can try doing public_path().'/uploads' 
    
                      $filename = $user->id.'.png';
                      $upload_success = Input::file('img')->move($destinationPath, $filename);
    
                      $user->img = $db_path;
                      $user->save();
    
             }
    
             if(isset($_POST['timesheet']))
                $this->timesheet_save($user->id, json_decode($_POST['timesheet']));
    
            return json_encode($status);
        }
    
    
        public function register()
        {
            $status  = new Status(NULL, NULL, NULL);
    
            if(isset( $_GET["username"]) &&  isset($_GET["p"]) &&  isset($_GET["email"]))
            {
    
    
                try
                {
    
                  $user = new User;
                  $user->username = $_GET["username"];
                  $user->email = $_GET["email"];
                  $password= $_GET["p"];
                  $user->password = Hash::make($password);
                  $user->active = 1;
                  $user->company_id = $this->company->id;
                  $user->save();
    
                  $customer = new Customer;
                  $customer->user_id = $user->id;
                  $customer->active = 1;
                  $customer->company_id = $this->company->id;
                  $user->Customer()->save($customer);
    
                  $status->result = TRUE;
                  $status->status = "success";
                  $status->msg = "Registration Account Successfull !!! <br/> you can now login into you account ...";
                }
                catch(Exception  $ex)
                {
    
                  $status->result = FALSE;
                  $status->status = "error";
                  $status->msg = $ex."Registration Proccess fail, please try again later your contact your  administrator";
    
                }
    
            }
            else
            {
                  $status->result = FALSE;
                  $status->status = "warning";
                  $status->msg = "Registration Proccess fail, please try again later your contact your  administrator";
    
    
            }
            echo json_encode($status);
    
        }
    
        public function login(){
    
            $status  = new Status(NULL, NULL, NULL);
            $status->result = FALSE;
    
            if(isset( $_GET["username"]) &&  isset($_GET["password"]) &&  isset($_GET["remember"]))
            {
    
                $username = $_GET["username"];
                $password = $_GET["password"];
                $remmeber =  $_GET["remember"];
    
    
                if (Auth::attempt(array('username' => $username, 'password' => $password), true))
                {  
    
                    if(Auth::user()->active == 1)
                    {
                        $status->result = TRUE;
                        $status->status = "logged";
                        $status->msg = "User logged with success";
                        $status->super_admin =  Auth::user()->is_super_admin();
                        $status->admin =  Auth::user()->is_admin();
                        $status->customer =  Auth::user()->is_customer();
                        $status->driver =  Auth::user()->is_driver();
                       
                         Auth::user()->session_id =  Session::token();
                         Auth::User()->save();

                         $status->token =  Session::token().'|||'.Auth::user()->remember_token;
                    }
                    else
                    {
    
                        $status->result = FALSE;
                        $status->status = "warning";
                        $status->msg = "your account is disable , please ask your administrator";
    
                    }
                }
                else
                {
                    $status->result = FALSE;
                    $status->status = "error";
                    $status->msg = "login fail, please verify your username or password";
    
                }
    
            }
            else
            {
                  $status->result = FALSE;
                  $status->status = "error";
                  $status->msg = "missing data for the login";
    
            }
    
            echo json_encode($status);
        }
    
    
        public function status()
        {
            echo "status";
            if (Auth::check())
            {
    
    
               if (Auth::guest())
                {
                     echo "is a guest";
                }
                else
                {
                    if(Auth::basic())
                    {
                    echo "is a basic";
    
    
                    }
                }
    
            }
            else{
                if (Auth::guest())
                {
                     echo "is a guest";
                }
                else
                {
                    if(Auth::basic())
                    {
                    echo "is a basic";
    
    
                    }
                }
            }
    
    
        }
    
        public function logout()
        {  
            Auth::logout();
        }
    
        public function logout_redirect()
        {  
            Auth::logout();
            $user= new User;
            return View::make('frontend.'.$this->company->theme.'.index', ['company' => $this->company, 'user' => $user]);
        }
    
    

        public function driver_logout()
        {  
           return json_encode($this->logout_driver());
        }


        public function ip()
        { 
            ACDPAuth.check();
            $client_ip  =$_SERVER['REMOTE_ADDR'];
            echo $client_ip;
        }
    
    
        function timesheet_save($id, $intervals)
        {
            $result = FALSE;
    
            $user_time_last=  UserTimes::where('user_id',$id)->orderBy('id','desc')->get()->first();
    
    
            foreach($intervals as $interval)
            {
                $user_times = new UserTimes;
                $user_times->fill((array)$interval);
                $user_times->user_id = $id;
                $result = $user_times->save();
                if(!$result)
                {
                    UserTimes::where('user_id','=', $id)->where('id','>',  $user_time_last->id)->delete();
                    return FALSE;
                }
            }
    
            if($result && count($user_time_last))
            {
                UserTimes::where('user_id','=',$id)->where('id','<=',  $user_time_last->id)->delete();
            }
    
            return $result;
    
        }
    
    
    }
