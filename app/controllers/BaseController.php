<?php
    class BaseController extends Controller {
    
        public $domain = NULL;
        public $jtable = NULL;
        public $company = NULL;
        public $root = '';
        public $master_company = NULL;
        public $mango_instance =  NULL;
      
        public function __construct()
        {
          
          $this->domain = $_SERVER["SERVER_NAME"];
          $this->company = Company::where("domain", "=",  trim($this->domain))->first();
          $this->root= $this->company->root_path.'/';
     
          $this->master_company = Company::where('type', '=', 'master')->first();
          if(count($this->master_company))
             $this->mango_instance = new MangoPay();
          
          
           
        }

        public function check_driver()
        {
          
          $client_token = Input::get('app_chauffer_token');

          $data =  explode("|||",$client_token);
   
          if(count($data) == 2)
          {
        
             $token=$data[1];
             $key = $data[0];
             $user = User::where("remember_token", "=", $token)->where("session_id", "=", $key)->first();
             Auth::login($user);
            return TRUE;
          }
          return FALSE;

        }

        public function logout_driver()
        {
          
          $client_token = Input::get('app_chauffer_token');

          $data =  explode("|||",$client_token);
   
          if(count($data) == 2)
          {
        
             $token=$data[1];
             $key = $data[0];
             $user = User::where("remember_token", "=", $token)->where("session_id", "=", $key)->first();
             $user->session_id = '';
             $user->save();
             Auth::logout();
             return TRUE;
          }

          return FALSE;

        }



        protected function setupLayout()
        {
            if (!is_null($this->layout))
            {
                $this->layout = View::make($this->layout);
            }
        }
    
        public function Jtable($data){
            $this->jtable = new Jtable($data);
            return $this->jtable->ToJson();
        }
    
    }
