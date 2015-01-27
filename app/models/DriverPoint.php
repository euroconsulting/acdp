<?php

class DriverPoint  {


    

    public $id;
    public $prev; 
    public $next; 

    public $original_distance =0;
    public $new_trip_distance  =0;
    public $total_distance_plus =0;

    public $original_time =0;
    public $new_trip_time  =0;
    public $total_time_plus  =0;


    public function __construct($id, $prev, $next)
    { 
        $this->id = $id;
        $this->prev = $prev;
        $this->next = $next;
        return $this;
    }


}
