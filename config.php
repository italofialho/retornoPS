<?php

function ConnectServer(){
	
	$userName = "";
	$password = "";
	$serverName = "";
	$dbName = "";
	
	$connectionInfo = array(
		"UID"=>$userName, 
		"PWD"=>$password, 
		"Database"=>$dbName, 
		"CharacterSet" => "UTF-8",
		"ReturnDatesAsStrings" => true);
		
	$conn = sqlsrv_connect( $serverName, $connectionInfo);
	
	if( $conn ) {
		 //echo "Connection established.";
	}else{
		 echo "Connection could not be established.";
		 die( print_r( sqlsrv_errors(), true));
	}
	
	return $conn;

}
	$conn = ConnectServer();
	$EmailPS	= '';
	$TokenPS	= '';
?>