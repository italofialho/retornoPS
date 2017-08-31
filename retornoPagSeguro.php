<?php
	require_once('config.php');
/*
 * Esse script de retorno não é imediato, ele somente é acionado quando o status da compra é modificado ou seja
 * A compra por padrão vem no status de Aguardando (1) e com isso é gerado o código de notificação
 * Ao mudar o status para 3 que é Pago ele envia novamente o $_POST com o status atualizado e assim faz o script continuar e depositar o cash do player
 */

// informações pagseguro
// não informe o token para ninguém!
$emailPagseguro = $EmailPS;
$tokenPagseguro = $TokenPS;

// é executado somente quando o pagseguro envia o post notificationType
if (isset($_POST['notificationType']) && $_POST['notificationType'] == 'transaction') {
    $url = 'https://ws.sandbox.pagseguro.uol.com.br/v2/transactions/notifications/'.$_POST['notificationCode'].'?email='.$emailPagseguro.'&token='.$tokenPagseguro;
    // inicia o curl e obtem os dados da url enviadas pelo pagseguro em formato XML
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $transaction = curl_exec($curl);
    curl_close($curl);
    //$transaction = simplexml_load_string($transaction);

    if ($transaction == 'Unauthorized') {
        //Insira seu código avisando que o sistema está com problemas, sugiro enviar um e-mail avisando para alguém fazer a manutenção

        exit; //Mantenha essa linha
    }

    // transforma o xml em objeto para facilitar obter os dados
    $transaction = simplexml_load_string($transaction);

    // gera um log do retorno do PagSeguro, retorna o que foi recebido do $_POST enviado pelo pagSeguro
    $name = 'log_pagSeguro.txt';
    $text = var_export($_POST, true);
    $file = fopen($name, 'a');
    fwrite($file, $text);
    fclose($file);

    // variaveis pagseguro
    $status = $transaction->status;
    $servidor = $transaction->items->item->id;
    $id_pagseguro = $transaction->reference;
    $quantidade = $transaction->items->item->quantity;
    $valor = $transaction->items->item->amount;
    $transactionID = $transaction->code;
    $compprador = $transaction->sender->name;
    $desc = $transaction->items->item->description;

    // quando o status enviado pela pagseguro por igual a pago (3) então executa a query
    if ($status == 3) {
		$name = 'log_pagSeguro.txt';
		$text = 'Executado com sucesso, cash e premium depositado!';
		$file = fopen($name, 'a');
		fwrite($file, $text);
		fclose($file);
            
		$sql2 = "UPDATE pagseguro_log SET status = '$status'";
		$res2 = sqlsrv_query($conn, $sql2);
	}	
?>