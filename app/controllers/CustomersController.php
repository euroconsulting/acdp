<?php
 
class CustomersController extends BaseController {
 
   
 
    public function index()
    {
          if(Auth::User()->is_super_admin())
               $customers =  Customer::with('user')->where('active','=', true)->get();
            else
            {
                $company_id = $this->company->id;
                $customers =   Customer::with('user')->whereHas('user', function($q) use ( $company_id)
                                {
                                    $q->where('company_id', '=', $company_id);

                                })->where('active','=', true)->get();
            }

      
          $this->Jtable($customers);
    }


    public function dashboard()
    {
       return View::make('customers.index');
    }

 

}