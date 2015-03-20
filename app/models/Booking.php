<?php
    
    
    
    class Booking extends Eloquent  {
    
        /**
         * The database table used by the model.
         *
         * @var string
         */
        public $timestamps = true;
        protected $table = 'bookings';
    
        /**
         * The attributes excluded from the model's JSON form.
         *
         * @var array
         */
    
    
          public function Customer()
          {
              return $this->belongsTo('Customer');
          }
    
          public function Offer()
          {
              return $this->belongsTo('Offer');
          }


          public function get_description()
          {
              $description = "";
              switch($this->Offer->trip_method)
              {
                  case 'TAB':
                  {
                    switch($this->Offer->calc_method)
                    {
                         case 1:
                               $description = 'BOOKING FROM '.$this->departing_address.' TO '.$this->arrival_address.' EST. TARIF AU KM : '.$this->distance.' KMS';
                            break;
    
                            case 2:
                                $description = 'BOOKING FROM '.$this->departing_address.' TO '.$this->arrival_address.' EST. TARIF AU KM : '.$this->durantion.' KMS';
                            break;
    
                            case 3:
                                  $description = 'BOOKING FROM '.$this->departing_address.' TO '.$this->arrival_address.' EST. TARIF AU KM : '.$this->distance.' KMS';
                            break;
    
    
                    }
    
                  }
                  break;
                  case 'TZG':
                  {
    
                    switch($this->Offer->calc_method)
                    {
                            case 1:
                                $description = 'BOOKING EN ZONE GEOGRAPHIQUE DEPUIS '.$this->departing_address.' EST. TARIF AU KM : '.$this->distance.' KMS';
                            break;
    
                            case 2:
                                $description = 'BOOKING EN ZONE GEOGRAPHIQUE DEPUIS '.$this->departing_address.' EST. TARIF AU HEURE : '.$this->durantion.' KMS';
                            break;
    
                            case 3:
                                $description = 'BOOKING EN ZONE GEOGRAPHIQUE DEPUIS '.$this->departing_address.' EST. TARIF AU FIXE KMS : '.$this->distance.' KMS';
                            break;
    
                    }
                  }
                  break;
                  case 'TLD':
                  {
                       switch($this->Offer->calc_method)
                       {
                            case 1:
                                $description = 'BOOKING LONGUE DISTANCE DEPUIS '.$this->departing_address.' EST. TARIF AU KM : '.$this->distance.' KMS';
                            break;
    
                            case 2:
                             $description = 'BOOKING LONGUE DISTANCE DEPUIS '.$this->departing_address.' EST. TARIF AU HEURE : '.$this->durantion.' KMS';
                            break;
    
                            case 3:
                                $description = 'BOOKING LONGUE DISTANCE DEPUIS '.$this->departing_address.' EST. TARIF AU FIXE KMS : '.$this->distance.' KMS';
                            break;
                      }
    
                  }
                  break;
              }
    
              return $description;
          }
    
    }
    
?>
