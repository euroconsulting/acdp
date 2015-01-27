<?php

class timeline
{
    public $hour;
    public $time;
    public $Mon;
    public $Tue;
    public $Wed;
    public $Thu;
    public $Fri;
    public $Sat;
    public $Sun;

   public function Timeline($hour)
   {    
       $this->hour = $hour;
       $value = "0:00";
       if($hour < 12)
          $value = $hour.':00 AM';
       else
          $value = $hour.':00 PM';

        $this->time = $value;
   }
}


?>

