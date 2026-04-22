<? 

header("Content-Type:text/html;charset=utf-8");


/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', false);
ini_set('display_startup_errors', false);


include("../../common.php");
include("../event_def.php");
include("kakao_bizppurio.php");//20221122 함수추가 

$name = $tools->filter($_POST['name2']);
if($name){
	$oid = $tools->filter($_POST['oid']);

/*	//[주문번호], [이름]으로 중복접수 확인 
	if ($db->cnt("cs_online_event", "where oid='$oid' and name='$name'") > 0) { 
		?>
		<script>
//			alert('이미 접수된 주문번호입니다.');
//			location.href="https://catchwell.com/b2c/event_page.html";
//			location.href="https://m.catchwell.com/b2c/test.html"; //TEST
		</script>
		<? 
	}
*/
//	$tel1 = $tools->filter($_POST['tel1']);
//	$tel2 = $tools->filter($_POST['tel2']);
//	$tel3 = $tools->filter($_POST['tel3']);
//	$tel = $tel1."-".$tel2."-".$tel3;
	$hp1 = $tools->filter($_POST['hp1']);
	$hp2 = $tools->filter($_POST['hp2']);
	$hp3 = $tools->filter($_POST['hp3']);
	$hp = $hp1."-".$hp2."-".$hp3;
	$hptalk = $hp1.$hp2.$hp3;
//	$email = $tools->filter($_POST['email']);
//	$email2 = $tools->filter($_POST['email2']);
//	$email = $email."@".$email2;
	$zip_new = $tools->filter($_POST['zip_new']);
	$add1 = $tools->filter($_POST['add1']);
	$add2 = $tools->filter($_POST['add2']);
	$japum = $tools->filter($_POST['japum']);
	$gdate = $tools->filter($_POST['gdate']);
	$shoppingmall = $tools->filter($_POST['shoppingmall']);
	$id = $tools->filter($_POST['id']);
	$nickname = isset($_POST['nickname']) ? $tools->filter($_POST['nickname']) : "";
	$content = $tools->filter($_POST['content']);
	if ($shoppingmall!='오늘의집') {
		$nickname='';
	}

	//GD함수 업로드
	include $_SERVER['DOCUMENT_ROOT']."/cw_as/lib/gd.php";
	
	// 파일업로드 
	if( $_FILES['bbs_file']['size'] > 0 ) {
		$EXT_CHECK = array("php", "php3", "htm", "html", "cgi", "perl", "mp4");	// 업로드 파일 제한 확장자 추가 가능
		if( $EXT_TMP = explode( ".", $_FILES['bbs_file']['name'])) {	 foreach ($EXT_CHECK as $value) { if( strstr( $value, strtolower($EXT_TMP[1]))) { $tools->errMsg( strtoupper($EXT_TMP[1])." 은 업로드 할수 없습니다." ); } }	}
		//if( $_FILES[bbs_file][size]  > 1024*1024*5) { $tools->errMsg("업로드 용량 초과입니다\\n\\n5메가 까지 업로드 가능합니다"); exit(); }
		$filename = substr($_FILES['bbs_file']['name'],-5);
		$fn = explode(".",$filename); 
		$EXT_TMP = $fn[1]; 
		$file_name	= time()."1.".$EXT_TMP;
		$sfile_name = $_FILES['bbs_file']['name'];
		list($width, $height)=getimagesize($_FILES['bbs_file']['tmp_name']); 
		if(max($width, $height) > 10240){
			$imgwidth=$width*(50/100);//width 값 
			$imgheight=$height*(50/100);//height 값 

			if(!@GDImageResize($_FILES['bbs_file']['tmp_name'], "../data_sniper/".$file_name, $imgwidth, $imgheight,NULL,90, strtolower($EXT_TMP))){ 
				$tools->errMsg("파일 업로드 에러(1)"); 
			} else { 
				@unlink($_FILES['bbs_file']['tmp_name']);	
			} 
		} else {
			//echo $ROOT_DIR."../data/".$file_name;
			if( !@move_uploaded_file($_FILES['bbs_file']['tmp_name'], "../data_sniper/".$file_name) ) { 
				$tools->errMsg("파일 업로드 에러(2)"); 
			} else { 
				@unlink($_FILES['bbs_file']['tmp_name']);	
			} 
		}
	} else {
		$file_name 	= "";
		$tools->errMsg("파일 업로드 에러(3)"); 
	}

	//특수문자제거
	$name = str_replace("'","",$name); 
	$add1 = str_replace("'","",$add1); 
	$add2 = str_replace("'","",$add2); 
	$id = str_replace("'","",$id); 
	$nickname = str_replace("'","",$nickname); 
	$oid = str_replace("'","",$oid); 
	$content = str_replace("'","",$content); 

	$query = "insert into cs_online_event_sniper set name='$name', hp='$hp', zip_new='$zip_new', add1='$add1', add2='$add2', japum='$japum', gdate='$gdate', shoppingmall='$shoppingmall', id='$id', nickname='$nickname', oid='$oid', content='$content', bbs_file='$file_name', udate=now(), status=0";
	if (mysqli_query($db->db_conn, $query) == false) {
		$tools->errMsg("접수 중 오류가 발생하였습니다."); 
	}

	function httpPost($url,$params)
	{
		$postData = '';

		foreach($params as $k => $v) 
		{ 
			$postData .= $k . '='.$v.'&'; 
		}
		$postData = rtrim($postData, '&');

		$headers = array(
			"Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
			"x-waple-authorization:MTE1NDQtMTU2ODY5NzgyMTU4MS1kYTEwOWZjMi01MmFmLTQ1YTEtOTA5Zi1jMjUyYWY2NWExMWE=" 
			);
		
		$ch = curl_init();  
		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_POST, count($postData));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
		
		$output=curl_exec($ch);
		
		curl_close($ch);
		
		if (USE_DEBUG) {
			//var_dump($output);
			//string(75) "{"result_message":"OK","result_code":"200","cmid":"2020112016081366431301"}"
		}

		return $output;

	}

	if (USE_KAKAOTALK) {
//20221122 bizppurio-------------------------------------------------------------------------------------------------------------------------------------
$DEBUG = 0;
$headers[0]="Accept:application/json"; 
$headers[1]="Content-Type:application/json"; 
$headers[2]="Authorization:Bearer";
$url = 'https://api.bizppurio.com/v3/message';

//추가
$token = getToken();
//print_r( $token);
$headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);

$button_list1 = array("name"=>"접수 조회", "type"=>"WL", "url_pc" =>"http://www.catchwell.com/b2c/event_query_sniper.html", "url_mobile"=>"http://www.catchwell.com/b2c/event_query_sniper.html");
$buttons = array($button_list1);

$at["message"] = "안녕하세요. 캐치웰입니다.\n"
.$name." 고객님의 사은품 이벤트 접수가 완료되었습니다.\n".
"응모하신 사은품은 월 2회 일괄 발송됩니다.\n".
"상품평 작성시 사진 2장과 50자 이상 작성해야 하며, 부정적 상품평은 제외될 수 있습니다.\n".
"* 이름: ".$name."\n".
"* 휴대폰: ".$hp."\n".
"* 모델명: ".$japum."\n\n".
"휴대폰번호로 접수상태를 조회할 수 있습니다.\n";
$at["senderkey"] = "44970513f96ceef0a7b532fa874d4697ca936dce";
$at["templatecode"] = "CAPI303";
$at["button"] = $buttons;

$content = array("at" => $at);

$data = array( );
$data["account"] = "catchwellota";//비즈뿌리오계정
$data["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
$data["type"] = "at";//메세지데이터타입
$data["from"] = "07077776752";//발신번호
$data["to"] = $hptalk;//수신번호
$data["content"] = $content;//메시지데이터

$json_data = json_encode($data, JSON_UNESCAPED_SLASHES);

if ($DEBUG)
{
	echo '<pre>';
	print_r($data);
	print_r($json_data);
	//print_r(urldecode($json_data));
	print_r(json_decode($json_data));
	echo '</pre>';
}

$Response = httpsPost($url, $json_data, $headers1);

$Ret_data = (json_decode($Response));
$code = $Ret_data->code;// 결과 코드 code 1000이면 성공
$description = $Ret_data->description;//결과 메세지 success 출력
$refkey_value = $Ret_data->refkey;//고객사에서 부여한 키
$messagekey = $Ret_data->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 

if ($DEBUG)
{
	echo '<pre>';
	print_r( "-------------------code------------" );
	echo '<br>';
	print_r($code);// 결과 코드 code 1000이면 성공
	echo '<br>';
	print_r($description); //결과 메세지 success 출력
	echo '<br>';
	print_r($refkey_value); //고객사에서 부여한 키
	echo '<br>';
	print_r($messagekey);//메세지키 
	echo '<br>';
	echo '</pre>';
}


//apistore----------------------------------------------------------------------------------------------------------------------------------------------	
/*		$talkmsg = "안녕하세요. 캐치웰입니다.
[{NAME}] 고객님의 사은품 이벤트 접수가 완료되었습니다.
응모하신 사은품은 월 2회 일괄 발송됩니다.
상품평 작성시 사진 2장과 50자 이상 작성해야 하며, 부정적 상품평은 제외될 수 있습니다.

* 이름: [{NAME}]
* 휴대폰: [{HP1}]
* 모델명 : [{MODEL}]

휴대폰번호로 접수상태를 조회할 수 있습니다.";

		$talkmsg = str_replace("[{NAME}]", $name, $talkmsg);
		$talkmsg = str_replace("[{HP1}]", $hp, $talkmsg);
		$talkmsg = str_replace("[{MODEL}]", $japum, $talkmsg);
		$parameters = array(
			'PHONE' => $hptalk, 
			'CALLBACK' => "07077776752", //발송인증된 번호만 사용가능
			'MSG' => $talkmsg, 
			'TEMPLATE_CODE' => "CAPI303", 
			'FAILED_TYPE' => "SMS", 
			'FAILED_SUBJECT' => "캐치웰", 
			'FAILED_MSG' => "캐치웰 이벤트접수 완료되었습니다. 접수조회:http://www.catchwell.com/b2c/event_query_sniper.html" , 
			'BTN_TYPES' => "웹링크", 
			'BTN_TXTS' => "접수 조회",              
			'BTN_URLS1' => "http://www.catchwell.com/b2c/event_query_sniper.html"
		);

		//발신번호 등록시 SENDNUMBER,COMMENT,PINTYPE을 먼저 입력하고 POST후 휴대전화로 PINCODE수신되면 PINCODE값 추가하여 인증하면됨
		$parametersendnum = array(
			'SENDNUMBER' => "07077776752",
			'COMMENT' => "캐치웰대표번호",
			'PINTYPE' => "SMS",
			'PINCODE' => "581732"
		);

		//알림톡 보낼때
		$ret = httpPost("http://api.apistore.co.kr/kko/1/msg/catchwell",$parameters);
		if (USE_DEBUG) {
//			var_dump($ret);//string(75) "{"result_message":"OK","result_code":"200","cmid":"2020112016081366431301"}"
//			echo "<br>";
		}

		$res1 = str_replace('{',"",$ret); 
		$res1 = str_replace('}',"",$res1);
		$res1 = str_replace('"',"",$res1);

		$res2 = explode(',', $res1); //result_message:OK,result_code:200,cmid:2020112016081366431301

		$result_message = "";
		$result_code = "";
		$cmid = "";

		for($i=0; $i<count($res2); $i++) 
		{
			//echo $res2[$i]."<br>"; 

			$res3 = explode(':', $res2[$i]);
			
			if ($res3[0] == "result_message") {
				$result_message = $res3[1];
			} else if ($res3[0] == "result_code") {
				$result_code = $res3[1];
			} else if ($res3[0] == "cmid") {
				$cmid = $res3[1];
			}
		}
		*/
//----------------------------------------------------------------------------------------------------------------------------------------------
		//var_dump($ret);//string(75) "{"result_message":"OK","result_code":"200","cmid":"2020112016081366431301"}"
		$result_message = "";
		$result_code = "";
		$cmid = "";
		//20221122
		$result_message = $description;
		$result_code = $code;
		$cmid = $messagekey;

		//insert to database 
		if( $db->insert('TB_EVENT_KAKAO',
			"	SENDING='REG_EVT',
				DATE=now(),
				OID_NO='$oid',
				PHONE='$hp',
				CMID='$cmid',
				MSG_RSLT='$result_message',
				RSLT='$result_code'
			"))
		{
		}


	}

?>

<script>
	alert('이벤트 응모에 접수 하였습니다.');
	location.href="https://catchwell.com/"; 
</script>

<?
}
?>