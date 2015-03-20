<?php
    
    
    
     class MangoPayController extends BaseController
     {
    
             public function index()
             {
    
                 $pagination = new MangoPay\Pagination(1, 8); // get 1st page, 8 items per page
                 $users =  $this->mango->Users->GetAll();
                 // echo json_encode($users);
    
             }
    
            //PAYEMENT EXCEPTION STARTS AT 800
           function pay_booking($id)
           {
               $status = new Status(FALSE, 'error', 'AN ERROR OCURRED WHEN PROCESSING THE PAYEMENT, PLEASE TRY AGAIN LATER');
    
               try
               {
                    //connect instance mangopay
                    $this->mango_instance->connect();
    
                    $booking  = Booking::find($id);
    
    
                    $mangopay_card_object_data =   json_decode($booking->mangopay_card_object_data);
    
                    $card = $this->mango_instance->mangopay->CardRegistrations->Get($mangopay_card_object_data->Id);
    
                    //we get the master company to get the wallets
                    //if master company has a customer collect wallet, a percentage will for the company
    
                    $master_wallets = $this->master_company->get_company_wallets();
    
                    $total_master_company_benefit = 0;
    
                    foreach($master_wallets->Records as $wallet)
                    {
                        if($wallet->customer_collect)
                        {
    
                          $company_benefit = round(floatval($wallet->customer_collect_percentage / 100)  * $booking->total, 2);
    
    
    
                           // echo "Mater Company benefit".$company_bennefit.'</br>';
                            $total_master_company_benefit += $company_benefit;
   
                            $status  = $this->mango_instance->pay_to_wallet($wallet, $card, $company_benefit, 10, $this->company->domain);
    
    
                            if($status->result)
                            {
    
    
                            }
                            else{
    
                                break;
                                return $status;
                            }
    
                        }
                    }
    
    
                    // we get the current company - to get the wallets
                    $current_company = Company::find($booking->company_id);
                    $company_wallets = $current_company->get_company_wallets();
    
                    $total_available = $booking->total -$total_master_company_benefit;
                    $total_company_benefit = 0;
    
                    foreach($company_wallets->Records as $wallet)
                    {
                        if($wallet->customer_collect)
                        {
                            $company_benefit = round(floatval($wallet->customer_collect_percentage / 100)  *  $total_available, 2);
                           // echo "Company_beneffit ".$company_bennefit.'</br>';
                            $total_company_benefit +=$company_benefit;
    
                                                     // create pay-in CARD DIRECT
        
    
                            $status  = $this->mango_instance->pay_to_wallet($wallet, $card, $company_benefit, 0, $this->company->domain);
                            if($status->result)
                            {
    
    
                            }
                            else{
    
                                break;
                                return $status;
                            }
    
    
                        }
                    }
    
                    //echo 'total company '.$total_company_bennefit;
                    //echo 'total distribuit '. ($total_master_company_bennefit + $total_company_bennefit);
                    if($status->result)
                    {
                         $booking->status = 'PAYED';
                         $booking->save();
                         $status->status =  'success';
                         $status->msg ='YOUR TRIP HAS BEEN BOOKED, PAYEMNT AS BEEN SUCCESSFULL';
                    }
                     else
                     {
                         $status->status =  'error';
                         $status->msg ='AN ERROR OCURRED WHEN PROCESSING THE PAYEMNT';
    
                     }
    
    
               }
               catch (MangoPay\ResponseException $ex) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='Payment gateway unavailable.';
                         $status->description = $ex->GetMessage();
                         $status->details = $ex;
                         echo $ex;
    
               }
               catch (MangoPay\Exception $ex) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='Payment gateway unavailable.';
                         $status->description = $ex->GetMessage();
                      echo $ex;
    
               }
               catch(Exception $ex)
               {
                   echo  $ex;
                    $status->exception =  800;
                    $status->exception_msg = 'AN ERROR OCURRED WHEN PROCESSING THE PAYEMENT, PLEASE TRY AGAIN LATER';
               }
    
             return json_encode($status);
    
           }
          //valida ta car token by unsing the form input at front-end with card data
           function validate_card($id)
           {
    
               $status = new Status(FALSE, 'error', 'Payment gateway unavailable.');
               $card_data = json_decode($_GET["data"]);
    
                $booking = Booking::find($id);
    
    
                if(count($booking) > 0)
                {
    
    
                    try {
    
                         $this->mango_instance->connect();
    
    
    
                         $card = json_decode($booking->mangopay_card_object_data);
                         $card = $this->mango_instance->mangopay->CardRegistrations->Get($card->Id);
    
                         $data = [];
                         $data['status'] = "true";
    
                         $curl = curl_init();
                         $url = 'https://homologation-webpayment.payline.com/webpayment/getToken';
    
                         $param = [];     
                         $param['data']=  $card->PreregistrationData;
                         $param['accessKeyRef'] = $card->AccessKey;
                         $param['cardNumber'] =  $card_data->cardNumber;
                         $param['cardExpirationDate'] = '1222';
                         $param['cardCvx'] = '123';
    
                         $param_str ="";
                         foreach($param as $key=>$value) 
                         { 
                             $param_str.= $key.'='.$value.'&'; 
    
                         }
    
                         rtrim($param_str, '&');
    
    
                         try
                         {
    
                           $url  =  htmlspecialchars($url);
    
                                 curl_setopt_array($curl, array(
                                 CURLOPT_RETURNTRANSFER      => 1,
                                 CURLOPT_POST                => count($param),
                                 CURLOPT_POSTFIELDS          => $param_str,
                                 CURLOPT_URL                 => $url,
                                 CURLOPT_SSL_VERIFYPEER      => false,
                                 CURLOPT_FAILONERROR         => true,
                             ));
    
                             $request = curl_exec($curl);
    
                             if(empty($request)):
                                 throw new \RuntimeException('cURL request retuened following error: '.curl_error($curl) );
                             endif;
    
                             curl_close($curl);        
    
    
                            if(count($request) > 0)
                            {
                                if(strrpos($request, "data=") >= 0)
                                { 
                                    $status->result = TRUE;
                                    $status->data =  $request;
    
                                    $card->RegistrationData = $request;
                                    //update the cardwith the token data received
    
    
                                    $card = $this->mango_instance->mangopay->CardRegistrations->Update($card);
    
    
    
                                    $status->status='success';
                                    $status->msg='Card token valid';
                                    $status->data = $card;
                                }
                                else
                                {
                                      $status->result = TRUE;
                                      $status->status='error';
                                      $status->msg='Validation card failure, please verify you data';
    
                                }
                            }
                            else
                            {
                                $status->result = TRUE;
                                $status->status='error';
                                $status->msg='Validation card failure, please verify you data';
    
    
                            }
                         }
                         catch (Exception $ex) {
    
                                $status->result = TRUE;
                                $status->status='error';
                                $status->msg='Payment Gatway unavaliable, please try gain later';
    
                         }
    
                    }
                    catch (MangoPay\ResponseException $ex) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='Payment gateway unavailable.';
                         $status->description = $ex->GetMessage();
                         $status->details = $ex;
                         echo $ex;
    
                     }
                     catch (MangoPay\Exception $ex) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='Payment gateway unavailable.';
                         $status->description = $ex->GetMessage();
    
    
                     }
    
                }
    
                return  json_encode($status);
    
          }
    
    
    
    
             public function company_edit($id)
             {
                 $company = Company::find($id);
                 $status = $this->check_mangopay_company_security($company);
    
                 if($status->result)
                 {
    
                     try {
    
    
                         $legal_user = new MangoPay\UserLegal();
                         $legal_user->Name = $company->name;
                         $legal_user->Email = $company->email;
                         $legal_user->Id = $company->id;
                         $legal_user->Tag = 'APPCHAUFFER';
                         $legal_user->LegalPersonType = 'BUSINESS';
                         $legal_user->HeadquartersAddress = $company->get_formated_address();
                         $legal_user->LegalRepresentativeFirstName = Auth::User()->first_name;
                         $legal_user->LegalRepresentativeLastName = Auth::User()->last_name;
                         $legal_user->LegalRepresentativeAddress  = $company->get_formated_address();
                         $legal_user->LegalRepresentativeEmail =$company->email;
                         $legal_user->LegalRepresentativeBirthday =  strtotime($company->trade_register_date);
                         $legal_user->LegalRepresentativeNationality=  $company->country_id;
                         $legal_user->LegalRepresentativeCountryOfResidence =  $company->country_id;
    
                         $this->mango_instance->connect();
                         $legal_user = $this->mango_instance->mangopay->Users->Create($legal_user);
    
                         $company->mangopay_user_id = $legal_user->Id;
                         $company->save();
                         $status->msg ='MangoPay Account Create with Success';
    
                     }
                     catch (MangoPay\ResponseException $e) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='An error ocurred when creating company, please try again later';
                         $status->description = $e->GetMessage();
                         $status->details = $e->GetErrorDetails();
    
                     }
                     catch (MangoPay\Exception $e) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='An error ocurred when creating company, please try again later';
                         $status->description = $e->GetMessage();
    
    
                     }
                 }
    
                 return json_encode($status);
    
             }
    
             public function company_wallets($id)
             {
                 $status = new Status(FALSE, 'error', 'company not found');
                 $company = Company::find($id);
    
                 if(count($company))
                 {
                    $data=  $company->get_company_wallets();
                    $data->ToJson();
                 }
                 else
                     return $status;
             }
    
    
             public function edit_wallet()
             {
    
                 $status = new Status(FALSE, 'error', 'Error creating wallet');
    
                 if(isset($_GET['wallet']))
                 {
                     $wallet = json_decode($_GET['wallet']);
    
                 }
                 else
                 {
                     $status->description = "no data received (POST) to create wallet to server";
                     return json_encode($status);
                 }
    
                 $company = Company::find($wallet->company_id);
                 $status = $this->check_mangopay_company_security($company);
    
                 if($status->result)
                 {
    
                     try {
    
    
                         $total_customer_collect_percentage = $company->get_total_collect_wallets();
    
                         $total_customer_collect_percentage_after = $total_customer_collect_percentage +  floatval($wallet->customer_collect_percentage);
    
                         if($total_customer_collect_percentage_after <= 100)
                         {
                             $mango_wallet = new MangoPay\Wallet();
    
    
                             if($wallet->Id > 0)
                             {
                                  $this->mango_instance->connect();
                                  $mango_wallet =  $this->mango_instance->mangopay->Wallets->Get($wallet->Id);
                                  $mango_wallet->Tag = $wallet->tag;
                                  $mango_wallet->Description = $wallet->description;
    
                                  $this->mango_instance->mangopay->Wallets->Update($mango_wallet);
    
    
                                  DB::update('update company_wallets set tag = ?, Description =?,
                                            customer_collect = ?, customer_collect_percentage = ? where Id = ?', 
                                            array($wallet->tag, $wallet->description, $wallet->customer_collect, $wallet->customer_collect_percentage, $wallet->Id));
    
                             }
                             else
                             {
                                 $mango_wallet->Tag = $wallet->tag;
                                 $mango_wallet->Owners = [];
                                 array_push($mango_wallet->Owners, $company->mangopay_user_id);
                                 $mango_wallet->Description = $wallet->description;
                                 $mango_wallet->Currency =  $wallet->currency; 
    
    
                                 //save on Mango PAY DB
                                 $this->mango_instance->connect();
                                 $mango_wallet = $this->mango_instance->mangopay->Wallets->Create($mango_wallet);
    
                                 //save on DB
                                 $CompanyWallet = new CompanyWallet();
                                 $CompanyWallet->fill((array)$wallet);
                                 $CompanyWallet->mangopay_user_id =  $company->mangopay_user_id;
                                 $CompanyWallet->Id = $mango_wallet->Id;
                                 $CompanyWallet->save();
    
    
                             }
    
                             $status->msg ='Wallet saved with success REF: '.$mango_wallet->Id;
                         }
                         else
                         {
                             $maximum_percentage_allowed = round(floatval(100 - $total_customer_collect_percentage),2);
                             $status->msg = 'Wallet Percentage is grether than 100%, maixmum allowed is: '.$maximum_percentage_allowed;
                             $status->status='error';
                         }
                     }
                     catch (MangoPay\ResponseException $ex) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='An error ocurred when creating company, please try again later';
                         $status->description = $ex->GetMessage();
                         $status->details = $ex;
                          // echo $ex;
    
                     }
                     catch (MangoPay\Exception $ex) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='An error ocurred when creating company, please try again later';
                         $status->description = $ex->GetMessage();
                         $status->details = $ex;
    // echo $ex;
                     }
                     catch (Exception $ex) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='An error ocurred when creating wallet, please try again later';
                         $status->description = $ex;
                        //   echo $ex;
                     }
                 }
    
                 return json_encode($status);
             }
    
             public function check_mangopay_company_security($company)
             {
    
    
                 $status = new Status(TRUE, 'success', NULL);
    
                 if(count(Auth::User()) > 0)
                 {
                     if($company  != null && Auth::User()->admin == 1 && $this->mango_instance->status == TRUE && (Auth::User()->Company->id == $company->id || Auth::User()->is_super_admin()))
                     {
                         $status->result = TRUE;
                     }
                     else
                     {
                         if(Auth::User()->admin == 0 || Auth::User()->Company->id != $company->id)
                         {
                              $status->result = FALSE;
                              $status->status ='error';
                              $status->msg ='You credentials are not alowed to create a Mango User';
    
                         }
    
                         if($company->id == NULL)
                         {
                               $status->result = FALSE;
                              $status->status ='error';
                              $status->msg ='Company not found, please use an existing company';
    
                         }
    
    
                         if($this->mango_instance->status == FALSE)
                         {
                              $status->result = FALSE;
                              $status->status ='error';
                              $status->msg ='Payment Gateway no setted or not working';
                         }
    
                     }
                 }
                 return $status;
             }
    
             public function companies()
             {   
    
                 $pagination = new MangoPay\Pagination(1, 50);
                 $mango = $this->mango_instance->connect();
                 $users =  $mango->Users->GetAll($pagination);
                 echo json_encode($users);
             }
    
             public function bank_account_edit($id)
             {
    
                 $status = new Status(FALSE, 'error', 'Error creating wallet');
    
                 if(isset($_GET['bank_account']))
                 {
                     $bank_account = json_decode($_GET['bank_account']);
    
                 }
                 else
                 {
                     $status->description = "no data received (POST) to editing or create bank account to server";
                     return json_encode($status);
                 }
    
                 $company = Company::find($bank_account->company_id);
                 $status = $this->check_mangopay_company_security($company);
    
                 if($status->result)
                 {
    
                     try {
    
                         $mango_bank_account = new MangoPay\BankAccount();
    
                        //conenct and get the user then add the bank account
                         $this->mango_instance->connect();
    
    
                         if($bank_account->Id > 0)
                         {
                             $status->msg ='bank account save with success -> REF: '.$bank_account->Id;
                         }
                         else
                         {
                             $user_id =intval($company->mangopay_user_id);
                             //Fill default data
                           //$mango_bank_account->Tag = 'custom tag';
                           //  $mango_bank_account->UserId= $user_id ;
                           //  $mango_bank_account->Type= $bank_account->Type;
                           //  $mango_bank_account->OwnerName = $bank_account->OwnerName;
                           //  $mango_bank_account->OwnerAddress ='AD';
                             //$mango_bank_account->CreationDate = strtotime(date('Y-m-d H:i:s'));
    
                             //$BankAccount =new MangoPay\BankAccount();
                             //$BankAccount->Type = "IBAN";
                             //$BankAccount->Details = new MangoPay\BankAccountDetailsIBAN();
                             //$BankAccount->Details->IBAN = "FR3020041010124530725S03383";
    
                             //$BankAccount->Details->BIC = "CRLYFRPP";
                             //$BankAccount->OwnerName = "Joe Bloggs";
                             //$BankAccount->OwnerAddress = "1 Mangopay Street";
                             //$result = $this->mango_instance->mango->Users->CreateBankAccount($user_id, $BankAccount);
    
    
                             $BankAccount =new MangoPay\BankAccount();
                             $BankAccount->Type = $bank_account->Type;
                             $BankAccount->Details = new MangoPay\BankAccountDetailsIBAN();
                             $BankAccount->Tag =  $bank_account->Tag;
                             $BankAccount->OwnerName =  $bank_account->OwnerName;
                             $BankAccount->OwnerAddress = $bank_account->OwnerAddress;
    
                             switch($BankAccount->Type)
                             {
                                 case 'IBAN':
                                     $BankAccount->Details = new MangoPay\BankAccountDetailsIBAN();
                                     $BankAccount->Details->IBAN = $bank_account->IBAN;
                                     $BankAccount->Details->BIC = $bank_account->BIC;
                                 break;
    
                                 default:
                                 break;
                             }
    
                             $Mangopay_BankAccount = $this->mango_instance->mangopay->Users->CreateBankAccount($user_id, $BankAccount);
    
                             //save bank in database just two fields
                                if(strlen($Mangopay_BankAccount->Id) > 0)
                                { 
    
                                     $Bank_Account = new CompanyBankAccount;
                                     $Bank_Account->Id = $Mangopay_BankAccount->Id;
                                     $Bank_Account->active = 1;
                                     $company->BankAccounts()->save($Bank_Account);
                                }
                             $status->msg ='bank accoutn created with success -> REF: '.$bank_account->Id;
                         }
    
                     }
                     catch (MangoPay\ResponseException $ex) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='An error ocurred when saving the  bank account, please try again later';
                         $status->description = $ex;
                        // echo $ex;
                     }
                     catch (MangoPay\Exception $ex) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='An error ocurred when saving the  bank account, please try again later';
                         $status->description = $ex;
                      //  echo $ex;;
    
                     }
                     catch (Exception $ex) {
    
                         $status->result = FALSE;
                         $status->status ='error';
                         $status->msg ='An error ocurred when saving the  bank account, please try again later';
                         $status->description = $ex;
    
                         //  echo $ex;
                     }
                 }
    
                return json_encode($status);
    
             }
    
             public function bank_accounts($type, $id)
             {
                 $status = new Status(FALSE, 'error', 'Company not found');
                 $entity = NULL;
    
                 switch($type)
                 {
                     case 'company':
                     {
                         $entity = Company::find($id);
                     }
                     break;
    
                     case 'customer':
                     break;
    
    
                 }
    
    
                 $result = $this->mango_instance->get_bank_accounts($entity);
                 $result->ToJson();
    
    
    
             }
    
             public function disable_bank_account($company_id, $id)
             {
                 $company = Company::find($company_id);
                 $status = $this->check_mangopay_company_security($company);
    
                 if($status->result)
                 {
    
                     if(count($company) && count($company->BankAccounts()))
                     {
    
                         $bank_account =CompanyBankAccount::where('company_id','=', $company->id)->where('Id','=', $id)->first();
    
                         DB::update('update company_bank_accounts set active = ? where Id = ? and company_id = ?',array(0, $id, $company->id));
    
                     }
                 }
    
                 return $this->bank_accounts($company_id, 'company');
    
            }
    
    
             public function disable_wallet($company_id, $id)
             {
                 $company = Company::find($company_id);
                 $status = $this->check_mangopay_company_security($company);
    
                 if($status->result)
                 {
    
                     if(count($company) && count($company->Wallets()))
                     {
    
                         DB::update('update company_wallets set active = ? where Id = ? and company_id = ?',array(0, $id, $company->id));
    
                     }
                 }
    
                 return $this->company_wallets($company_id);
    
            }
    
    
     }
?>
