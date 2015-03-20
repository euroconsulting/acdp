<?php
    


class Invoice
{
    
     public $total = 0;
     public $sub_total = 0;
     public $tva = 0;
     public $itens = [];
     public $customer = NULL;
}


class InvoiceItem
{
    public $id;
    public $quantity;
    public $title;
    public $description;
    public $total;

}

?>

