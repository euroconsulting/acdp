<?php
    
    class DocumentsController extends BaseController {
    
        //public function __construct()
        //{
        //    //$this->beforeFilter('auth.company',  array('only' => array('index') ));
        //}
    
  //  
        public function dashboard()
        {
            return View::make('documents.index');
        }
    
    
        public function index()
        {


            $sql = "select CONCAT(users.first_name,' ',users.last_name) as Entity, 
		            bookings.id as id,
		            'booking' as Origin,
		            'invoice' as Type,
		            get_booking_title(offers.trip_method, offers.calc_method, bookings.departing_address, bookings.arrival_address, bookings.distance, bookings.duration, bookings.total) as Title ,  
		            bookings.updated_at as Created
		            from `users` 
                    inner join customers on(customers.user_id = users.id)
                    inner join bookings on(customers.id = bookings.customer_id)
                    inner join offers on (offers.id = bookings.offer_id) where bookings.`status`='PAYED' order by bookings.updated_at DESC";



            $documents = DB::select($sql);


            echo $this->Jtable($documents);
        }
    
        
        public function getpdf($origin, $id)
        {
            

            $mode = 'edit';
            $document = new Document;
    

         
            switch($origin)
            {
                case 'booking':
                {
                   $document = $this->get_booking_invoice($id);

                }
                break;

            }
           
       
           return View::make('documents.document.invoice', ['document' => $document , 'mode' => $mode ]);

        }


        public function get($origin, $id)
        {
          

            $mode = 'edit';
            $document = new Document;
    

         
            switch($origin)
            {
                case 'booking':
                {
                   $document = $this->get_booking_invoice($id);

                }
                break;

            }
           
       
           return View::make('documents.document.index', ['document' => $document , 'mode' => $mode ]);
        }
            
        public function by_id($id)
        {
            echo "id: ".$id;
    
        }
    

        public function get_booking_invoice($id)
        {
            $document = new Document;
            $booking = Booking::find($id);
               
            $company = Company::find($booking->company_id);
            $customer = Customer::find($booking->customer_id);
                     
                     
            $document->id = $booking->id.date('dmY');
            $document->created = $document->id.date('dmY');
            $document->company = $company;
            $document->customer = $booking->Customer->User;
            $document->invoice = new Invoice();
            $document->invoice->id =  $booking->id;
            $document->invoice->sub_total = $booking->sub_total;
            $document->invoice->tva = $this->company->tva;
            $document->invoice->total = $booking->total;
            $customer_name = $booking->Customer->User->Fullname;
            
            $item = new InvoiceItem();
            $item->id = $booking->id;
            $item->quantity = 1;
            $item->title =  $booking->get_description();
            $item->sub_total =  $booking->sub_total;
            $item->customer =  $customer_name;
            array_push($document->invoice->itens, $item);   

            return $document;
        }
    }
