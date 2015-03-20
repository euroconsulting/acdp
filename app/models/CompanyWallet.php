<?php
    
    class CompanyWallet extends Eloquent  {
    
        /**
         * The database table used by the model.
         *
         * @var string
         */


        protected $table = 'company_wallets';
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
        
        protected $guarded = array('mangopay_user_id');
        protected $fillable =[];


    
         public function Company()
         {
             return $this->belongsTo('Company');
         }
  
    
    
    
    }
?>