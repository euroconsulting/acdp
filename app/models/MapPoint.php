<?php

class MapPoint  {


    public $location = NULL;
    public $lat;
    public $lng;



    public function __construct($lat, $lng, $location, $time, $tag)
    { 
        $this->time = $time;
        $this->tag = $tag;
        $this->location = $location;
        $this->lat = $lat;
        $this->lng = $lng;
        return $this;
    }


}
