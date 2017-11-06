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
                $this->writeOnFile("Transaction Unauthorized:", $this->transaction);
                exit;
            } else {
                $this->transaction = simplexml_load_string($temp_Transaction);
            }
        } else {
            array_push($this->log, "Failed to validate notification code.");
        }
    }

    private function writeOnFile($mensagem, $data = NULL, $file = "logs/pagseguro._pslog")
    {
        echo("File: ".$file);
        $f = fopen($file, (file_exists($file)) ? 'a' : 'w') or die("Unable to open file!");
        fwrite($f, "$mensagem");
        if(!is_null($data)){
            fwrite($f, "\n".json_encode($data));
        }
        fwrite($f, "\n---------------------------\n\n");
        fclose($f);
    }
    
    private function httpPost($url, $params)
    {
        $postData = '';
        foreach ($params as $k => $v) {
            $postData .= $k . '=' . $v . '&';
        }
        
        $postData = rtrim($postData, '&');
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        
        $output = curl_exec($ch);
        
        curl_close($ch);
        return $output;
        
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
            
            $code          = $this->transaction->code;
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

            $transactionStatus = $this->transaction->status;
            if ($transactionStatus == 1) {
                $transactionStatusDesc = 'Aguardando pagamento';
            }else if ($transactionStatus == 2) {
                $transactionStatusDesc = 'Em análise';
            }else if ($transactionStatus == 3) {
                $transactionStatusDesc = 'Paga';
            }else if ($transactionStatus == 4) {
                $transactionStatusDesc = 'Disponível';
            }else if ($transactionStatus == 5) {
                $transactionStatusDesc = 'Em disputa';
            }else if ($transactionStatus == 6) {
                $transactionStatusDesc = 'Devolvida';
            }else if ($transactionStatus == 7) {
                $transactionStatusDesc = 'Cancelada';
            }
            
            $paymentType = $this->transaction->paymentMethod->type;
            if ($paymentType == 1){
                $paymentTypeDesc = "Cartão de crédito";
            }else if ($paymentType == 2){
                $paymentTypeDesc = "Boleto";
            }else if ($paymentType == 3){
                $paymentTypeDesc = "Débito online (TEF)";
            }else if ($paymentType == 4){
                $paymentTypeDesc = "Saldo PagSeguro";
            }else if ($paymentType == 5){
                $paymentTypeDesc = "Oi Paggo";
            }else if ($paymentType == 7){
                $paymentTypeDesc = "Depósito em conta";
            }
            
            $paymentMethod = $this->transaction->paymentMethod->code;
            if ($paymentMethod == 101){
                $paymentMethodDesc = "Cartão de crédito Visa";
            }else if ($paymentMethod == 102){
                $paymentMethodDesc = "Cartão de crédito MasterCard";
            }else if ($paymentMethod == 103){
                $paymentMethodDesc = "Cartão de crédito American Express";
            }else if ($paymentMethod == 104){
                $paymentMethodDesc = "Cartão de crédito Diners.";
            }else if ($paymentMethod == 105){
                $paymentMethodDesc = "Cartão de crédito Hipercard";
            }else if ($paymentMethod == 106){
                $paymentMethodDesc = "Cartão de crédito Aura";
            }else if ($paymentMethod == 107){
                $paymentMethodDesc = "Cartão de crédito Elo";
            }else if ($paymentMethod == 108){
                $paymentMethodDesc = "Cartão de crédito PLENOCard";
            }else if ($paymentMethod == 109){
                $paymentMethodDesc = "Cartão de crédito PersonalCard";
            }else if ($paymentMethod == 110){
                $paymentMethodDesc = "Cartão de crédito JCB";
            }else if ($paymentMethod == 111){
                $paymentMethodDesc = "Cartão de crédito Discover";
            }else if ($paymentMethod == 112){
                $paymentMethodDesc = "Cartão de crédito BrasilCard";
            }else if ($paymentMethod == 113){
                $paymentMethodDesc = "Cartão de crédito FORTBRASIL";
            }else if ($paymentMethod == 114){
                $paymentMethodDesc = "Cartão de crédito CARDBAN";
            }else if ($paymentMethod == 115){
                $paymentMethodDesc = "Cartão de crédito VALECARD";
            }else if ($paymentMethod == 116){
                $paymentMethodDesc = "Cartão de crédito Cabal";
            }else if ($paymentMethod == 117){
                $paymentMethodDesc = "Cartão de crédito Mais";
            }else if ($paymentMethod == 118){
                $paymentMethodDesc = "Cartão de crédito Avista";
            }else if ($paymentMethod == 119){
                $paymentMethodDesc = "Cartão de crédito GRANDCARD";
            }else if ($paymentMethod == 120){
                $paymentMethodDesc = "Cartão de crédito Sorocred";
            }else if ($paymentMethod == 201){
                $paymentMethodDesc = "Boleto Bradesco";
            }else if ($paymentMethod == 202){
                $paymentMethodDesc = "Boleto Santander";
            }else if ($paymentMethod == 301){
                $paymentMethodDesc = "Débito online Bradesco";
            }else if ($paymentMethod == 302){
                $paymentMethodDesc = "Débito online Itaú";
            }else if ($paymentMethod == 303){
                $paymentMethodDesc = "Débito online Unibanco";
            }else if ($paymentMethod == 304){
                $paymentMethodDesc = "Débito online Banco do Brasil";
            }else if ($paymentMethod == 305){
                $paymentMethodDesc = "Débito online Banco Real";
            }else if ($paymentMethod == 306){
                $paymentMethodDesc = "Débito online Banrisul";
            }else if ($paymentMethod == 307){
                $paymentMethodDesc = "Débito online HSBC";
            }else if ($paymentMethod == 401){
                $paymentMethodDesc = "Saldo PagSeguro";
            }else if ($paymentMethod == 501){
                $paymentMethodDesc = "Oi Paggo";
            }else if ($paymentMethod == 701){
                $paymentMethodDesc = "Depósito em conta - Banco do Brasil";
            }else if ($paymentMethod == 702){
                $paymentMethodDesc = "Depósito em conta - HSBC";
            }

            $query = $this->pdo->prepare("INSERT INTO `PagSeguro` (`Code`, `Reference`, `Status`, `LastEventDate`, `GrossAmount`, `FeeAmount`, `NetAmount`, `SenderEmail`, `ItemCount`, `ItemID`, `ItemDesc`, `ItemQtd`, `ItemAmount`, `JSON`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
            $query->bindValue(1, $code);
            $query->bindValue(2, $reference);
            $query->bindValue(3, $status);
            $query->bindValue(4, $lastEventDate);
            $query->bindValue(5, $grossAmount);
            $query->bindValue(6, $feeAmount);
            $query->bindValue(7, $netAmount);
            $query->bindValue(8, $senderEmail);
            $query->bindValue(9, $itemCount);
            $query->bindValue(10, $itemID);
            $query->bindValue(11, $itemDesc);
            $query->bindValue(12, $itemQtd);
            $query->bindValue(13, $itemAmount);
            $query->bindValue(14, json_encode($this->transaction));
            
            if($query->execute()){
                $file = "logs/ps_".$code."._pslog";
                $this->writeOnFile("Dados Verificados! Salvando copia dos dados da transação no arquivo ".$file." Status: ".$transactionStatusDesc);
                $this->writeOnFile("Copia dos dados da transação: ".$code, $this->transaction, $file);
                return true;
            }else{
                echo("Failed");
            }

        } else {
            array_push($this->log, "Failed to get transaction data. Error: " . $this->transaction->error->message);
            $this->writeOnFile("Failed to get transaction data. Error: " . $this->transaction->error->message);
        }
    }
    
    public function execute()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validatingRequisition();
        }
    }
    
}

include("db.php");
$db = new DataBase();
$pdo = $db->openConnection();
$ps = new PagSeguro($pdo);
$ps->execute();



?>