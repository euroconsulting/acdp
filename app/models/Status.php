<?php
    
    class Status  {
    
        public $result;
        public $status;
        public $msg;
        public $data = [];
        public function __construct($_result, $_status, $_msg)
        {
            $this->result = $_result;
            $this->status = $_status;
            $this->msg = $_msg;
            return $this;
        }
    
    
    }
    
?>

