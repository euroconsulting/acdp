<?php

class Jtable {

	public $Records;
    public $Result = "OK";
    public $TotalRecordCount=0;


    public function __construct($data)
    { 
        $this->Records = $data;
        $this->TotalRecordCount =count($data);
        return $this;
    }

    public  function get(){
         
        $this->Records = $data;
        $this->TotalRecordCount =count($data);
        return $this;

    }
    
    public function ToJson(){
        
        echo json_encode($this);

    }

}

?>
