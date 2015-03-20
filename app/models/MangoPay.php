<?php

class MangoPay
{

     public $status = TRUE;
     public $user = NULL;
     public $wallets = [];
     public $bank_accounts = [];
     public $mangopay = NULL;
     public function __construct()
     {
          

     }

     public function connect(){
         
          $this->mangopay = new MangoPay\MangoPayApi();
          $this->mangopay->Config->ClientId = 'frenchconnection2015';
          $this->mangopay->Config->ClientPassword = '5CsHKZGBDxv7btyoeMXCbLBKT59VqsJUMSedLTXhv1WveqNBOo';
      
         $this->mangopay->Config->TemporaryFolder = app_path().'/models/temp';    
         // $this->mangopay->Config->TemporaryFolder = '/temp'; 
         // $this->mangopay->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
            return $this->mangopay;
     }

     public function create_update_user($user)
     {
        $status = new Status(FALSE, 'error', 'An error ocurrend creating user or updating user');

        try 
        {
            $Exists = FALSE;
            $naturalUser = new MangoPay\UserNatural();
           

            if(strlen($user->mangopay_user_id) > 0)
            {
                $Exists = TRUE;
                $naturalUser =$api->Users->Get($user->mangopay_user_id);
            }  
            
            $naturalUser->Tag = 'APPCHAUFFER';
            $naturalUser->Email = $user->email;
            $naturalUser->FirstName = $user->first_name;
            $naturalUser->LastName= $user->last_name;
            $naturalUser->Address =$user->get_formated_address();
            $birthdate = date($user->birthdate);
            $naturalUser->Birthday =  strtotime($birthdate);
            $naturalUser->Nationality = $user->country_id;
            $naturalUser->CountryOfResidence = $user->country_id;
            $naturalUser->Occupation='CUSTOMER';
            $naturalUser->IncomeRange = '6';
            $naturalUser->ProofOfIdentity = 'none';
            $naturalUser->ProofOfAddress = 'none';

            $this->connect();

            if($Exists)
            {
               $naturalUser = $this->mangopay->Users->Update($naturalUser);
            }
            else
            {
            
               $naturalUser = $this->mangopay->Users->Create($naturalUser);
               $user->mangopay_user_id = $naturalUser->Id;
               $user->save();
            }
            
            $status->result = TRUE;
            $status->status ='SAVED';

        }
        catch (MangoPay\ResponseException $ex) 
        {
    
            $status->result = FALSE;
            $status->status ='error';
            $status->msg ='An error ocurrend creating user or updating user';
            $status->description = $ex->GetMessage();
            $status->details = $ex;
          
        }
        catch (MangoPay\Exception $ex) {
    
            $status->result = FALSE;
            $status->status ='error';
            $status->msg ='An error ocurrend creating user or updating user';
            $status->description = $ex->GetMessage();
           
        }
        catch (Exception $ex) {
            
            $status->result = FALSE;
            $status->status ='error';
            $status->msg ='An error ocurrend creating user or updating user';
            $status->description = $ex->GetMessage();
        }
        
           
        return $status;
     }

     public function get_token_card($user, $booking)
     {
        $status = new Status(FALSE, 'success', '');
        try
        {
            $card_registration =  new MangoPay\CardRegistration();
            $card_registration->UserId = $user->mangopay_user_id;
            $card_registration->Currency = 'EUR';
      
            $card_registration = $this->mangopay->CardRegistrations->Create($card_registration);
  
            $status->result = TRUE;
            $status->status = 510;
          
            $booking->mangopay_card_object_data = json_encode($card_registration);
            $booking->save();
            $status->data =$card_registration;
        }
        catch (MangoPay\ResponseException $ex){
    
                $status->result = FALSE;
                $status->status = 506;
                $status->msg ='An error ocuurend create the registractiion card token';
                $status->description = $ex;
                echo $ex;
                
        }
        catch (MangoPay\Exception $ex){
    
                $status->result = 506;
                $status->status ='error';
                $status->msg ='An error ocuurend create the registractiion card tokrne';
                $status->description = $ex;
                               echo $ex;
               
        }
        catch (Exception $ex) {
    
                $status->result = 505;
                $status->status ='error';
                $status->status =500;
                $status->msg =$ex;
                $status->description = $ex;
                               echo $ex;
               
        }
        return $status;
     }

     public function check_user_mangopay($id)
     {
         //Codes for mango payemetn start at 100 
         // 500-> User not found || when user not found means database online or problem in connection
         // 501-> User has not mangopay account
         // 502-> User  has mangopay but is not working, means user as id of mangopay on database but in the mangopay api we dosent exist
 
         
        $status = new Status(FALSE, 'error', 'payement gatwey not available at moment 3');
        try
        {
            $user = User::find($id);

            if(count($user) > 0)
            {
                if(strlen($user->mangopay_user_id) > 0)
                {
                   
                    $this->connect();
                   
                   // normaly when a user register on page will got a mangopay account, unless any process fails during the  process
                    $mangopay_user = $this->mangopay->Users->Get($user->mangopay_user_id);
               
                    if($mangopay_user != NULL && $mangopay_user->Id > 0)
                    {
                       
                        $status->result = TRUE;

                       
                    }
                    else
                    {
                        
                        $status->result = FALSE;
                        $status->status =502;
                        $status->msg ='Your account reported an error, please contact you support';
                    }
               
                }
                else
                {
                   $result = $this->create_update_user($user);

                   if($result->result)
                   {
                        $status =  $this->check_user_mangopay($id);
                   }
                   else
                   {
                        $status->status =500;
                        $status->msg ='Payement gateway not available please try later 4';
                   }

                }
            }
            else{
                
                $status->result = FALSE;
                $status->status =500;
                $status->msg ='Payement gateway not available please try later 5';
                

            }
        }
        catch (MangoPay\ResponseException $ex){
    
                $status->result = FALSE;
                $status->status ='error';
                $status->msg ='An error ocuurend when getting the Bank Accounts, please try again later';
                $status->description = $ex;
                  echo $ex;
                
        }
        catch (MangoPay\Exception $ex){
    
                $status->result = FALSE;
                $status->status ='error';
                $status->msg ='An error ocuurend when getting the Bank Accounts, please try again later';
                $status->description = $ex;
                  echo $ex;
               
        }
        catch (Exception $ex) {
    
                $status->result = FALSE;
                $status->status ='error';
                $status->msg ='An error ocuurend when getting the Bank Accounts, please try again later';
                $status->description = $ex;
                  echo $ex;
               
        }

        return $status;
     }




     public function pay_to_wallet($wallet, $card, $amount, $fees, $domain)
     {
     
        $status = new Status(FALSE, 'error', 'an error ocurred doing the payment');
        
        try
        {

           //Build the parameters for the request
            $payIn = new MangoPay\PayIn();
            $payIn->CreditedWalletId = intval($wallet->Id);
            $payIn->AuthorId =  intval($card->UserId);
            $payIn->PaymentType = "CARD";
            $payIn->PaymentDetails = new MangoPay\PayInPaymentDetailsCard();
            $payIn->PaymentDetails->CardType = "CB_VISA_MASTERCARD";
            $payIn->DebitedFunds = new \MangoPay\Money();
            $payIn->DebitedFunds->Currency = "EUR";
            $payIn->DebitedFunds->Amount = $amount;
            $payIn->Fees = new MangoPay\Money();
            $payIn->Fees->Currency = "EUR";
            $payIn->Fees->Amount = 0;
            $payIn->ExecutionType = "DIRECT";
            $payIn->ExecutionDetails = new MangoPay\PayInExecutionDetailsDirect();
            $payIn->ExecutionDetails->SecureModeReturnURL = "http://".$domain;
            $payIn->ExecutionDetails->CardId = intval($card->CardId);
     

          
            // create Pay-In
             $createdPayIn = $this->mangopay->PayIns->Create($payIn);
           
            // if created Pay-in object has status SUCCEEDED it's mean that all is fine
            if ($createdPayIn->Status == 'SUCCEEDED') {
            
                $status->result  = TRUE;
                $status->status  = 'success';    
                $status->msg  = 'Paymenet executed';   
                $status->data  = $createdPayIn;   
            }
            else 
            {
                $status->result  = FALSE;
                $status->status  = 'error';    
                $status->msg  = 'Paymenet failure';    
                $status->data  = $createdPayIn;               
            }
        } 
        catch (MangoPay\ResponseException $ex) 
            {
    
                $status->result = FALSE;
                $status->status ='error';
                $status->msg ='An error ocuurend when getting the Bank Accounts, please try again later';
                $status->description = $ex;
     
               
            }
            catch (MangoPay\Exception $ex) {
    
                $status->result = FALSE;
                $status->status ='error';
                $status->msg ='An error ocuurend when getting the Bank Accounts, please try again later';
                $status->description = $ex;
                 
            }
            catch (Exception $ex) {
    
                $status->result = FALSE;
                $status->status ='error';
                $status->msg ='An error ocuurend when getting the Bank Accounts, please try again later';
            
            }
          
        return $status;

     }

     public function get_user_by_company($id)
     {
      
         $company = Company::find($id);
         if(count($company))
         {
          
             try
             {
               $this->connect();
               $this->user = $this->mangopay->Users->Get($company->mangopay_user_id);
              
             }
             catch(Exception  $ex)
             {
                 

             }

         }
         return $this->user;
     }
    

     public function get_wallets($mangopay_user_id)
     {
      
        if(strlen($mangopay_user_id) > 0)
        {
            try
            {
                $this->connect();
                $this->wallets = $this->mangopay->Users->GetWallets($mangopay_user_id);
               
            }
            catch(Exception  $ex)
            {
                 
            }
        }

         return  $this->wallets;
     }


     public function get_bank_accounts($company)
     {
        $status = new Status(FALSE, 'success', 'Accounts are 100% OK');

      
        if(count($company) && strlen($company->mangopay_user_id) > 0)
        {
            try
            {    
             
               $status->result = TRUE;
                $this->connect();

                $pagination = new MangoPay\Pagination(1, 100);
                $bank_accounts = $this->mangopay->Users->GetBankAccounts($company->mangopay_user_id, $pagination );
               
         
                foreach($bank_accounts as $mango_bank_account)
                {
                    $BankAccount = $company->BankAccounts()->find($mango_bank_account->Id);

                    if(count($BankAccount) > 0  && $BankAccount->active)
                        array_push($this->bank_accounts, $mango_bank_account);
                }
                
            }
            catch (MangoPay\ResponseException $ex) 
            {
    
                $status->result = FALSE;
                $status->status ='error';
                $status->msg ='An error ocuurend when getting the Bank Accounts, please try again later';
                $status->description = $ex;
                 echo $ex;
            }
            catch (MangoPay\Exception $ex) {
    
                $status->result = FALSE;
                $status->status ='error';
                $status->msg ='An error ocuurend when getting the Bank Accounts, please try again later';
                $status->description = $ex;
                 echo $ex;
            }
            catch (Exception $ex) {
    
                $status->result = FALSE;
                $status->status ='error';
                $status->msg ='An error ocuurend when getting the Bank Accounts, please try again later';
                $status->description = $ex;
                echo $ex;
            }
        }
        
         $jdata = new Jtable($this->bank_accounts);
         $jdata->Status = $status;
         return $jdata;
     }

}


?>