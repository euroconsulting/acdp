<?php
    
    class CompaniesController extends BaseController {
    
        public function __construct()
        {
            //$this->beforeFilter('auth.company',  array('only' => array('index') ));
        }
    
    
        public function dashboard()
        {
            return View::make('companies.company.index');
        }
    
    
        public function index()
        {
            echo json_encode(Company::all());
        }
    
    
        public function companies()
        {
            $data = Company::all();
            echo json_encode($data);
    
        }
    
        public function drivers($id)
        {
            $data = Company::find($id);
            echo json_encode($data->drivers);
    
        }
    }
