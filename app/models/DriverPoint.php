<?php

class DriverPoint  {


    
    public $driver;
    public $id;
    public $prev; 
    public $next; 

    public $original_distance =0;
    public $total_distance_plus =0;

    public $original_time =0;
    public $total_time_plus  =0;

    public $cars =[];

    public function __construct($driver, $prev, $next, $car)
    { 
        $this->driver = $driver;
        $this->prev = $prev;
        $this->next = $next;
        $this->cars =  $car;
        return $this;
    }


}
