<?php
    
    class CompanyBankAccount extends Eloquent  {
    
        /**
         * The database table used by the model.
         *
         * @var string
         */


        protected $table = 'company_bank_accounts';
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
        


         public function Company()
         {
             return $this->belongsTo('Company');
         }

    }
?>