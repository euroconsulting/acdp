<?php
    class BaseController extends Controller {
        
        public $jtable = NULL;
        /**
         * Setup the layout used by the controller.
         *
         * @return void
         */
        public function __construct()
        {
          
        }
        protected function setupLayout()
        {
            if (!is_null($this->layout))
            {
                $this->layout = View::make($this->layout);
            }
        }

        public function Jtable($data){
            $this->jtable = new Jtable($data);
            return $this->jtable->ToJson();
        }

    }
