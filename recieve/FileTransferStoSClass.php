<?php
// NOI DUNG CAN LAM
// FileTransferStoSClass.php chuyen file giua cac server (ps:OK da lam xong)
// Phan mo rong: Ngoai chuyen file ra, chuyển dữ liệu JSON (mảng liên kết) giữa các máy chủ có xét đến API (em dang bi mac phan nay)
// send & reciev class  File Transfer server to server


// Send side: File read ⇒ Read data JSON encode convert ⇒ curl POST Transfer
// Reciev side: GetHTTP_request POST data read ⇒ Read data JSON decode convert ⇒ File write


//-------------------
// File Transfer between to servers
//-------------------


// Tao class van chuyen file duoc chi dinh giua cac server

class FileTransferStoS{
// send para
 	private $SFname;  // chi dinh dia chi noi den va ten file can chuyen
	private $Surl;   // dia chi den cua API Script URL

// receive para
    private $RFname;  // nhan file va save lai
 
//construct function
    public function __construct()	{

        $this->SFname 	= "";			//Khởi tạo
        $this->Surl 	= "";			//Khởi tạo
        $this->RFname 	= "";			//Khởi tạo

    }

//for Debug
    public function fGet_para()		{

		echo	"SFname=".$this -> SFname."<br>\n";
		echo	"Surl=".$this -> Surl."<br>\n";
		echo	"RFname=".$this -> RFname."<br>\n";

	}


	
//===================  phia gui (Sender)  ===================

	public function	fSendSetFname( $SFname ){
// chi dinh file chuyen truoc khi chuyen
	
		$this -> SFname = $SFname;	// setup dia chi va ten file gui
   }

	public function	fSendSetURL( $Surl ){
     // nhap di chi API Script URL phia ben nhan
		
		$this -> Surl = $Surl;	//cai dat API Script URL phia ben nhan
	}

	public function	fSendFile($token){
//dung POST de chuyen file
// Nội dung tệp sẽ ở định dạng JSON. Trong trường hợp là Binary, hãy chuyển đổi sang base64 và sau đó chuyển đổi sang định dạng JSON.
// gia tri tra lai: output API phia doi dien

    	// doc file
		if(! file_exists($this->SFname)) {
			return	fCurlReturnError(404, "read file search error!! ");	} // kiem tra xem file co ton tai hay khong

		if(! $fp = fopen($this->SFname,"r")) {
			return	fCurlReturnError(404, "read file open error!!  ");	}	//file Open

		$src = fread($fp,filesize($this->SFname));		//Read File

		$src = base64_encode($src);		//if bainary file then base64_encode
		$fp = fclose($fp);

    // setup file duoc gui
		$fname = explode( '/', $this->SFname );

		$fn = $fname[count($fname)-1];

		$arr= array("fname" => $fn, "fsrc" => $src);

		$res = $this -> fSendJsonPOST($this->Surl,$token,$arr);

		return $res;
	}

//EM dang khong xu ly duoc phan send JSON nay mong moi nguoi giup do, em cam on!!!
	public function	fSendJson($token,$arr){
//van dung POST de chuyen data
// du lieu mang dinh dang o kieu JSON
// gia tri tra lai: output API phia doi dien
		
    // gui mang duoi dang du lieu JSON
		$res = $this -> fSendJsonPOST($this->Surl,$token,$arr);

		return $res;
	}



	//===================  phia ben nhan (Receiver)  ===================
	public function	fReceiveSetFname( $RFname ){
	// Chỉ định vị trí lưu trữ tệp và tên trước khi nhận tệp

		$this -> RFname = $RFname;		
	}

	public function	fReceiveFile( $token){
//dung POST de chuyen file
// Nội dung tệp sẽ ở định dạng JSON. Trong trường hợp là Binary, hãy chuyển đổi sang base64 và sau đó chuyển đổi sang định dạng JSON.
// gia tri tra lai: output API phia doi dien

    // Token check
		$this -> fCheckExistGetAllHeaders();

		$Headers = getallheaders();

    // Token ben trong Header check
		$src = "";			
		$token_ticket = 0;	
	//Nếu nó trở thành 1, bạn có thể thấy rằng có "X-Json-Token" trong thông tin tiêu đề.

		foreach ($Headers as $key => $value) {
			// Token check
			if(strcasecmp("X-API-TOKEN", $key) == 0){
				$token_ticket = 1;
				if($value!==$token)	return	$this->fCurlReturnError(401, 	"illegal token!!");	 //Token sai
			}

			$src .= $key." : ". $value . "\n";
			//echo "$key: $value\n";
		}
		// Nhan API
		$arr= $this -> fGetHTTP_request();

		// Save file
		$this->RFname = $this->RFname . $arr["fname"];

		if(! $fp = fopen($this->RFname,"w")) {
			return	$this->fCurlReturnError(510, 	"file write error!!  ");	}	//fileWrite error

		$arr["fsrc"] = base64_decode($arr["fsrc"]);		//if bainary file then base64_decode

		$src = fwrite($fp,$arr["fsrc"]);		 //File save

		$fp = fclose($fp);

		return	$src;

	}

//EM dang khong xu ly duoc phan send JSON nay mong moi nguoi giup do, em cam on!!!
	public function	fReceiveJson( $token){
		// Token check
		$this -> fCheckExistGetAllHeaders();

		$Headers = getallheaders();

		//Token ben trong Header check
		$src="";			
		$token_ticket = 0;  
		//Nếu nó trở thành 1, bạn có thể thấy rằng có "X-Json-Token" trong thông tin tiêu đề

		foreach ($Headers as $key => $value) {
			// Token check
			if(strcasecmp("X-API-TOKEN", $key) == 0){
				$token_ticket = 1;
				if($value!==$token)	return	$this->fCurlReturnErrorArr(401, "illegal token!!"); //Token sai
			}
		}
		if($token_ticket == 0)	return	$this->fCurlReturnErrorArr(401,	"not found token!!");	//Khong tim thay token

		// APIの受信 //Nhan API

		$arr= $this -> fGetHTTP_request();

		return	$arr;

	}


	public function fCurlReturnErrorArr($err_code, $details){
//Nhận lý do gây ra lỗi, trả về phản hồi JSON và thoát
	//	*
	//	* @param $err_code
	//	* @param $details
	//	*
	//	* @return なし
	//	*

	  switch($err_code){

	    case "200":
	      $res_str = "OK";
	      break;
	    case "400":
	      $res_str = "Bad Request";
	      break;

	    case "401":
	      $res_str = "Unauthorized";
	      break;

	    case "404":
	      $res_str = "Not Found";
	      break;

	    case "500":
	      $res_str = "Internal Server Error";
	      break;

	    case "503":
	      $res_str = "Service Unavailable";
	      break;

	    case "510":
	      $res_str = "File Write Error";
	      break;

	    default:
	      $res_str = "";
	      break;

	  }

	$data["status"] = array(
		'code'      => $err_code,
		'result'    => $res_str,
		'details'   => array( 'message'   => $details )
		);

	return $data;

     }


//Day la phan e tach ra de lam phan mo rong tu cai function fCurlReturnErrorArr ben tren
	public function fCurlReturnError($err_code, $details){
	//Trả về dữ liệu mảng của fCurlReturnErrorArr trong JSON

	$data = $this -> fCurlReturnErrorArr($err_code, $details);

	$encoded_json_data = json_encode($data);

		$madr = "nguyen.dynamix@gmail.com";
		$sendmsg = "data=".$data."\n\n";
		$this -> fMailSend($madr,"fCurlReturnError debug",$sendmsg,$madr);

	return $encoded_json_data;


     }

	//---------------------------  private function  ----------------------------------


	// PHP Version 5.3.3  cURL Information 7.19.7

	//　Chức năng ĐĂNG dữ liệu định dạng JSON sang định dạng Kết nối
	private function fSendJsonPOST($url,$token,$arr) {
	// Gửi dữ liệu mảng của $ url đến $ url được chỉ định bằng cách POST
	// Mảng $arr giá trị POST　vi du　$arr = array('mid'=>'b3a5hr','age'=>27,'sex' => 'f');
	// HTTP Header application/x-www-form-urlencoded  Khi gửi với kiểu thông thường

		$curl = curl_init($url);

		$surl = explode("//", $url);
		$surl = explode("/", $surl[1]);
		$domain = $surl[0];		// chi lay domain 
		//them option cho headers
		$headers = array(
			"HOST: ".$domain,
			"Content-Type: application/json",
			"X-API-TOKEN: ".$token
		);

		$options = array(
		  //HEADER
		  CURLOPT_HTTPHEADER => $headers,
		  //Method
		  //CURLOPT_POST => true,		//POST
		  CURLOPT_CUSTOMREQUEST => 'POST',		//POST
		  // curl_exec()Trả về kết quả dưới dạng một chuỗi
		  CURLOPT_RETURNTRANSFER => true,
		  //body
		  CURLOPT_POSTFIELDS => json_encode($arr)
		);

		curl_setopt_array($curl, $options);

	//	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );

		curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);

		// request
		$res = $contents = curl_exec($curl);
//var_dump($res);

		if($contents==FALSE)	$res = 	curl_error($curl);
		else					$res =	json_decode($contents);

		curl_close($curl);
//return	"Test fSendJsonPOST";		exit;	/*

		return $res;	//phia doi dien gui Messages
//*/
	}



	private function fGetHTTP_request() {

	//	Nhận yêu cầu HTTP Tương ứng với dữ liệu văn bản hoặc dữ liệu JSON
	//	Những gì được gửi、Content-type: application/x-www-form-urlencoded
	//	Hoac la Content-type: application/json　
	//	Tham khao tu：https://qiita.com/Kunikata/items/2b410f3cc535e4104906
	//	JSON Shaping tool https://tools.m-bsys.com/development_tooles/json-beautifier.php

	    $content_type = explode(';', trim(strtolower($_SERVER['CONTENT_TYPE'])));
	    $media_type = $content_type[0];

	    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $media_type == 'application/json') {
	        // application/json trong truong hop duoc gui den thi xu ly
	        $request = json_decode(file_get_contents('php://input'), true);		//Đầu ra dưới dạng một mảng kết hợp
	    }
	    else {
	        // application/x-www-form-urlencoded trong truong hop duoc gui den thi xu ly
	        $request = $_REQUEST;
	    }

	    return $request;
	}


	private function fCheckExistGetAllHeaders(){

	// Header Nhận thông tin
	// Dung PHP getallheaders() khi khong hoat dong duoc
	// copied from: https://gist.github.com/ryonakae/9f5e99c92ac9986791166438aa14cc53
	//

	if (!function_exists('getallheaders')) {
		 function getallheaders() {
			 $headers = array();

			 foreach ($_SERVER as $name => $value) {
				 if (substr($name, 0, 5) == 'HTTP_') {
					 $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				 }
			 }

			 return $headers;
		 }
	 }
	 else	return getallheaders();

	}


	//------ class end  -------
/**/


}




?>
