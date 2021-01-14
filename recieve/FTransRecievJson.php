<?php
// FTransferBTSClass.php su dung class nay nhan file va luu file
// send & reciev class  File Transfer between to servers

//Reciev side: GetHTTP_request POST data read ⇒ Read data JSON decode convert ⇒ File write

	require_once("./FileTransferStoSClass.php");

	$sts = new FileTransferStoS();

	// Token duoc chi dinh POST nhan lay JSON
	
	$res = $sts -> fReceiveJson("abcd1234");

	$ok = 0;
	if($res["Command"] == "Reg") $ok = 1;
	if($res["Name"] == "Hieu") $ok += 1;
	

	if ($ok == 2)	$rtxt = $sts -> fCurlReturnError(200, "$res["Data"]");		// Complete
	else 		$rtxt = $sts -> fCurlReturnError(503, $ok);			// "Service Unavailable"

	//tra ve cURL ban dau

	echo $rtxt;

	exit;

?>
