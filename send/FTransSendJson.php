<?php
// FileTransferStoSClass dung class nay doc file、sau do JSON Encode 
// curl voi URL duoc chi dinh gui bang POST

//Send side: File read ⇒ Read data JSON encode convert ⇒ curl POST Transfer

	require_once("./FileTransferStoSClass.php");

	$sts = new FileTransferStoS();

	$sts -> fSendSetURL( "http://localhost/filetransfer/recieve/FTransReciev.php" );	//URL phia nhan

//	$sts -> fGet_para();		exit;		// for debug

	$arr = array("Command"=>"Reg" , "Name"=>"Hieu", "Data"=>"hohohslpw92k29zk");

	$token = "abcd1234";

	$jres = $sts -> fSendJson($arr,$token);    //doc file roi gui bang POST

	var_dump($jres);

	exit;

?>
