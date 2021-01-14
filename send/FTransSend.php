<?php
// FileTransferStoSClass dung class nay doc file、sau do JSON Encode 
// curl voi URL duoc chi dinh gui bang POST

//Send side: File read ⇒ Read data JSON encode convert ⇒ curl POST Transfer

	require_once("./FileTransferStoSClass.php");

	$sts = new FileTransferStoS();

	$sts -> fSendSetFname("./test.txt");    // doc file duoc chi dinh

	$sts -> fSendSetURL( "http://localhost/filetransfer/recieve/FTransReciev.php" );    //URL phia nhan

	//$sts -> fGet_para();		exit;		// for debug

	$jres = $sts -> fSendFile( "abcd1234" );    // doc file roi gui bang POST

	var_dump ($jres);

	exit;

?>
