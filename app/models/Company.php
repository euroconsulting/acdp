<?php
    class Company extends Eloquent  {
    
        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'companies';
        protected $guarded = ['id'];
        protected $fillable =[];

        public $reservation_status =FALSE;
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
     
    

          public function Wallets()
          {
                return $this->hasMany('CompanyWallet');
          }

          public function BankAccounts()
          {
                return $this->hasMany('CompanyBankAccount');
          }


          public function Driver()
          {
                return $this->hasMany('Driver');
          }
    
          public function User()
          {
                return $this->hasMany('User');
          }
          public function Cars()
          {
                return $this->hasMany('Car');
          }
    
          public function get_formated_address()
          {
                return $this->address.', nº '.$this->address_number.' '.$this->address_code_postal.' - '.$this->country_id;
    
          }

          public function get_document_contact_details()
          {
                $formated_details =  $this->address.', nº '.$this->address_number.' </br>'.$this->address_code_postal.' - '.$this->country_id;
                if(strlen($this->phone1) > 0)
                {
                    $formated_details.=' </br> P: '.$this->phone1;

                }
    
                return $formated_details;
          }
    
    
          public function is_mangopay()
          {
              if(strlen($this->mangopay_user_id) > 0 && $this->mangopay_user_id > 0)
              {
    
                return TRUE;
              }
              else
              {
                return FALSE;
    
              }
    
          }
    
          public function reservation_status()
          {
 
              //Check if there are Drivers and Cars, and if module payment is active and rules mathched
              if(count($this->Driver()) > 0 && count($this->Cars()) > 0 && (strlen($this->mangopay_user_id) > 0))
              {
                    $wallets = $this->get_company_wallets();
                    $this->reservation_status =  $wallets->Status;
                  
              }
              
          }


          public function is_master(){
    
              if($this->type == 'master')
                return TRUE;
              else
                return FALSE;
    
          }
    
        public function get_company_wallets()
        {  
            
             $data = [];
    
             $mango_ctrl = new  MangoPayController();
             $status = $mango_ctrl->check_mangopay_company_security($this);
             $jtable_wallets = new Jtable($data);
    
             if($status->result)
             {
    
                    $payment_needed = FALSE;
                    $percentage_collect = 0;
                    //if company is type client payement , company must have 1  wallet, or  collect wallets that makes 100% of the payment
                    //in this case will define a varialble to sum the percentage of collects wallets to ensure that is good to collect all 100% of payement
                    $collect_percentage= 0;
                    $active_customer_collect = FALSE;
    
                    if($this->type == 'client')
                        $payment_needed = TRUE;
                    try {
    
                        $wallets = $mango_ctrl->mango_instance->get_wallets($this->mangopay_user_id);
  
                       $active_wallets = [];
                       foreach($wallets as $wallet)
                       {
                            $db_wallet = CompanyWallet::where('Id', '=', $wallet->Id)->first();
                            $wallet->Type ='Type Normal'; 
    
                            if(count($db_wallet) > 0 && $db_wallet->active)
                            {
                                $wallet->customer_collect =  $db_wallet->customer_collect;
                                $wallet->customer_collect_percentage =  $db_wallet->customer_collect_percentage;
    
                                if($db_wallet->customer_collect)
                                {
                                    $active_customer_collect = TRUE;
                                    $wallet->Type ='Type Collect | '. $db_wallet->customer_collect_percentage.'%';
    
    
                                    $collect_percentage =$collect_percentage + $db_wallet->customer_collect_percentage;
                                }
                                
                                array_push($active_wallets, $wallet);
                             }
                             else
                             {
                                    $wallet->customer_collect = 0;
                                    $wallet->customer_collect_percentage = 0;
                             }
                       }
    
                       if(count($wallets)  > 0)
                       {
                           if($payment_needed)
                           {
                               if($active_customer_collect== FALSE)
                               {
                                   $status->status = FALSE;
                                   $status->status = "danger";
                                   $status->msg ='You must defined a wallets with customer collect, or multiple where total of collected value is 100%';
    
    
                               }
                               else
                               {
                                   if($collect_percentage < 100)
                                   {    
                                       $status->status = FALSE;
                                       $status->status = "warning";
                                       $status->msg ='You must defined a wallets with customer collect, or multiple where total of collected value is 100%, you just are getting '.$collect_percentage.'% of the customer bill';
    
                                   }
                                   else
                                   {    $status->status = FALSE;
                                        $status->status = "success";
                                        $status->msg ='Walets are 100% OK';
                                   }
    
                               }
    
                           }
                           else
                           {    $status->status = "success";
                                $status->msg ='Walets are 100% OK';
    
                           }
                       }
                       else
                       {
    
                        $status->result = FALSE;
                        $status->status ='warning';
    
                        if($payment_needed)
                            $status->msg ='You have no Wallet Collect defined, you are not able to collect payements from your customer, reservation module will not be available in the FRON END';
                        else
                            $status->msg ='You have no Wallet Collect defined, you are not able to collect payements from your customer';
    
                        $status->description = NULL;
                        $status->details = NULL;
    
    
                       }
    
                  
                        $jtable_wallets->Records = $active_wallets;
                        $jtable_wallets->TotalRecordCount = count($wallets);
    
    
                    }
                    catch (MangoPay\ResponseException $ex) {
    
                        $status->result = FALSE;
                        $status->status ='error';
                        $status->msg ='An error ocurred when creating company, please try again later';
                        $status->description = $ex->GetMessage();
                        $status->details = $ex->GetErrorDetails();
    
                    }
                    catch (MangoPay\Exception $ex) {
    
                        $status->result = FALSE;
                        $status->status ='error';
                        $status->msg ='An error ocurred when creating company, please try again later';
                        $status->description = $ex->GetMessage();
                        $status->details = $ex->GetErrorDetails();
    
                    }
            }
    
            $jtable_wallets->Status =  $status;
            //echo json_decode($jtable_wallets);
    
            return $jtable_wallets;
    
        }
    
        public function get_total_collect_wallets()
        {

               $wallets = CompanyWallet::where("mangopay_user_id",'=', $this->mangopay_user_id)->where('active','=', 1)->get();
  
               $total_customer_collect_percentage = 0;
       
               foreach($wallets as $wallet)
               {
                   //echo 'C->'.$wallet->customer_collect;
                   //echo '-P->'.$wallet->customer_collect_percentage;

                   if($wallet->customer_collect == 1)
                        $total_customer_collect_percentage  = $total_customer_collect_percentage + $wallet->customer_collect_percentage;
               }
               return $total_customer_collect_percentage;
    
        }
    
    }
