<?php

class Company extends Eloquent  {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'companies';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
      public function user()
   {
        return $this->hasMany('Driver');
   }
   

}
