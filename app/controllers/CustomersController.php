<?php
 
class CustomersController extends BaseController {
 
    public function __construct()
    {
        //$this->beforeFilter('auth.company',  array('only' => array('index') ));
    }
 
    public function index()
    {
          $customers =  Customer::with('user')->get();
          $this->Jtable($customers);
    }


    public function dashboard()
    {
       return View::make('customers.index');
    }

     public function customer_dashboard($id)
    {
       $customer = Customer::find($id);
       return View::make('customers.customer.index', ['customer' => $customer]);
    }

    
}