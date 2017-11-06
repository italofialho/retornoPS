<?php

class CheckOut
{
    private $pdo;
    private $email;
    private $token;
    private $checkout;
    public $checkOutCode;
    private $log = array();
    
    function __construct($pdo)
    {
        $this->pdo   = $pdo;
        $this->email = "";
        $this->token = "";
    }
    
    private function testeCheckOut()
    {
        $param    = array(
            'token' => $this->token,
            'email' => $this->email,
            'currency' => "BRL",
            'itemId1' => rand(),
            'itemDescription1' => 'Desc: ' . rand(),
            'itemQuantity1' => 'ItemQuantity',
            'itemAmount1' => 'ItemPrice',
            'itemWeight1' => 'ItemWeight',
            'reference' => rand(),
            'senderEmail' => $this->email,
            'senderName' => 'YourName'
            
        );
        $checkout = $this->httpPost("https://ws.sandbox.pagseguro.uol.com.br/v2/checkout", $param);
        
        if ($checkout != 'Unauthorized') {
            $checkout = simplexml_load_string($checkout);
        }
        
        return $checkout;
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
    
    private function rand()
    {
        return substr(md5(microtime(true)), 0, 5);
    }
    
    public function execute()
    {
        $checkout = $this->testeCheckOut();
        if (isset($checkout->code)) {
            $this->checkOutCode = $checkout->code;
        }
    }
}

?>