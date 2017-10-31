<?php

class PagSeguro
{
    private $pdo;
    private $email;
    private $token;
    private $url;
    private $transaction = NULL;
    private $notificationCode = NULL;
    private $log = array();
    
    function __construct($pdo)
    {
        $this->pdo   = $pdo;
        $this->email = "";
        $this->token = "";
    }
    
    private function parseData()
    {
        if ($this->getPSNotification()) {
            $this->url = sprintf("https://ws.sandbox.pagseguro.uol.com.br/v2/transactions/notifications/%s?email=%s&token=%s", $this->notificationCode, $this->email, $this->token);
            
            $curl = curl_init($this->url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $temp_Transaction = curl_exec($curl);
            curl_close($curl);
            
            if ($temp_Transaction == 'Unauthorized') {
                array_push($this->log, "Transaction Unauthorized");
            } else {
                $this->transaction = simplexml_load_string($temp_Transaction);
            }
        } else {
            array_push($this->log, "Failed to validate notification code.");
        }
    }
    
    private function getPSNotification()
    {
        if (isset($_POST['notificationType']) && !empty($_POST['notificationType']) && $_POST['notificationType'] == 'transaction') {
            if (isset($_POST['notificationCode']) && !empty($_POST['notificationCode'])) {
                $this->notificationCode = $_POST['notificationCode'];
                return true;
            }
        }
    }
    
    private function validatingRequisition()
    {
        $this->parseData();
        if (!is_null($this->transaction) && !isset($this->transaction->error)) {
            
            $status        = $this->transaction->status;
            $date          = $this->transaction->date;
            $reference     = $this->transaction->reference;
            $lastEventDate = $this->transaction->lastEventDate;
            $grossAmount   = $this->transaction->grossAmount;
            $feeAmount     = $this->transaction->feeAmount;
            $netAmount     = $this->transaction->netAmount;
            $senderEmail   = $this->transaction->sender->email;
            $itemCount     = $this->transaction->itemCount;
            $items         = $this->transaction->items;
            $itemID        = $this->transaction->items->item->id;
            $itemDesc      = $this->transaction->items->item->description;
            $itemQtd       = $this->transaction->items->item->quantity;
            $itemAmount    = $this->transaction->items->item->amount;
            
        } else {
            array_push($this->log, "Failed to get transaction data. Error: " . $this->transaction->error->message);
        }
    }
    
    public function execute()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validatingRequisition();
        }
    }
    
}



?>