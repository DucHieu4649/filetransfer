<?php
// FTransferBTSClass.php su dung class nay nhan file va luu file
// send & reciev class  File Transfer between to servers

//Reciev side: GetHTTP_request POST data read ⇒ Read data JSON decode convert ⇒ File write

	require_once("./FileTransferStoSClass.php");

	$sts = new FileTransferStoS();

	$sts -> fReceiveSetFname("./");

	$res = $sts -> fReceiveFile("abcd1234");

	if ($res)	$rtxt = $sts -> fCurlReturnError(200, "");// Complete
	else 		$rtxt = $sts -> fCurlReturnError(510, "");		// Fwrite error

	//tra ve cURL ban dau

	echo $rtxt;

	exit;

?>
