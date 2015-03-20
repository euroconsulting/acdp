<?php
    
    class AmenitiesController extends BaseController {
    
        public function __construct()
        {
            //$this->beforeFilter('auth.company',  array('only' => array('index') ));
        }
    
    
        public function dashboard()
        {
            return View::make('amenities.index');
        }
    
    
        public function index()
        {
            echo json_encode(Amenitie::all());
        }
    
    
        public function by_id($id)
        {
            echo "id: ".$id;
    
        }
    
    }
