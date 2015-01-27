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

        public function __construct()
        {
        
         
          $this->beforeFilter('auth.admin', ['only' => 'logout2']);

          //that means that rule that check if is public will be called just on status method
          $this->beforeFilter('auth.public', ['only' => 'status']);

         
             
        }

        public function index()
        {
            return View::make('home/dashboard');
        }



        public function create()
        {
            $user = new User;
            $user->first_name = "firstname";
            $user->last_name="lastname";
            $user->username = "user1";
            $user->password = Hash::make('user1');
            $user->email  ="user1@euroconsulting.com";
            $user->save();
        }

        public function login($user, $pass){
   
            if (Auth::attempt(array('username' => $user, 'password' => $pass)))
            {
               return View::make('backoffice.dashboard');
            }
            else
                echo "false";
        }

        public function status()
        {
            echo "status";
            if (Auth::check())
            {
              echo json_encode(Auth::user());
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
           echo "logout";
            Auth::logout();
        }


        public function ip()
        { 
           ACDPAuth.check();
            $client_ip  =$_SERVER['REMOTE_ADDR'];
            echo $client_ip;
        }
    }
