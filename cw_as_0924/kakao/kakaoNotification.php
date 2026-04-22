<?php  

header("Content-Type:text/html;charset=utf-8");

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once "./CKakaoNotificationTalk.php";

include("../common.php");
include("kakao_bizppurio.php");//20221122 함수추가 


$customerName = isset($_POST['customer_name']) ? $_POST['customer_name'] : "";
$customerPhone = isset($_POST['customer_phone']) ? $_POST['customer_phone'] : "";
$regNumber = isset($_POST['register_number']) ? $_POST['register_number'] : "";
$modelName = isset($_POST['model_name']) ? $_POST['model_name'] : "";
$brokenType = isset($_POST['broken_type']) ? $_POST['broken_type'] : "";
$adminMemo = isset($_POST['admin_memo']) ? $_POST['admin_memo'] : "";
$customerCenter = "070-7777-6752";
$parcelCo = "CJ대한통운";
$parcelNo = isset($_POST['parcel_num_return']) ? $_POST['parcel_num_return'] : "";

// check customer phone number
/*
$customerPhone = preg_replace("/[^0-9]/", "", $customerPhone);
if( !preg_match("/^01[0-9]{8,9}$/", $customerPhone) )
{
    //echo "<script>alert('[카카오알림톡] 전송할 수 없는 번호입니다.'); history.back(-1);</script>";
    echo "<script>alert('$customerPhone'); history.back(-1);</script>";
    return;
}
*/

$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "";

/*
echo "==========================================="."<br/>\n";
echo "고객명 : ".$customer."<br/>\n";
echo "접수번호 : ".$registerNo."<br/>\n";
echo "택배사 : ".$deliveryCo."<br/>\n";
echo "송장번호 : ".$trackingNum."<br/>\n";
*/
//apistore--------------------------------------------------------------------------------------------------------------------------------------------
/*
$keyCode = "MTE1NDQtMTU2ODY5NzgyMTU4MS1kYTEwOWZjMi01MmFmLTQ1YTEtOTA5Zi1jMjUyYWY2NWExMWE=";
$clientID = "catchwell";
$defaultCallback = "07077776752";
$noti = new CKakaoNotificationTalk( $keyCode, $clientID, $defaultCallback );

$talkMsg_applicationDone = "안녕하세요. 캐치웰입니다.\n\n".$customerName." 고객님의 A/S 접수가 완료되었습니다.
\n* 접수번호 : ".$regNumber."\n* 제품명 : ".$modelName."\n* 고장증상 : ".$brokenType.
"\n\n소중한 고객님의 제품, 빠른 시일내에 처리될 수 있도록 최선을 다하겠습니다.\n\n관련 문의사항은 고객센터\n(".$customerCenter.")를 이용해 주세요.\n";

$talkMsg_fixDone = "안녕하세요. 캐치웰입니다.\n\n".$customerName." 고객님이 접수하신 제품의 AS가 완료되어 금일 발송처리 되었습니다.
\n* 접수번호 : ".$regNumber."\n* 제품명 : ".$modelName."\n* 고장증상 : ".$brokenType."\n* A/S내역 : ".$adminMemo.
"\n\n* 택배사 : ".$parcelCo."\n* 송장번호 : ".$parcelNo.
"\n\n관련 문의사항은 고객센터\n(".$customerCenter.")를 이용해 주세요.";

$smsMsg_applicationDone = "[캐치웰] ".$customerName." 님의 A/S 접수가 완료되었습니다. \n\n■ 접수번호 : ".$regNumber;
$smsMsg_fixDone = "[캐치웰] 고객님의 A/S가 완료되어 금일 발송되었습니다. \n\n■ 송장번호 : ".$regNumber."(CJ대한통운)";

$asLink = "http://backup.catchwell.com/cw_as/online_as/online_as_customer_search.php?searchBy=searchbyRegisterNo&searchData=".$regNumber;
$homapageLink_m = "https://m.catchwell.com";
$homapageLink = "https://www.catchwell.com";

$msgBody_applicationDone = [
    "phone" => $customerPhone,         // 수신자 전화번호
    "callback" => $defaultCallback,   // 발신자 전화번호 
    "reqdate" => "",                  // 예약발송 기능("20191225100000" 형식), 비워두면 즉시발송
    "msg" => $talkMsg_applicationDone,
    "template_code" => "CAPI007",     // 템플릿으로 등록 승인된 템플릿의 코드값
    "failed_type" => "SMS",
    "failed_subject" => "[캐치웰]",
    "failed_msg" => $smsMsg_applicationDone,
    "btn_types" => "웹링크,웹링크",         // 카카오 알림톡 버튼타입     
    "btn_txts" => "AS 조회,홈페이지",       // 카카오 알림톡 버튼이름
    "btn_urls1" => $asLink.",".$homapageLink_m,
    "btn_urls2" => $asLink.",".$homapageLink
];

$msgBody_fixDone = [
    "phone" => $customerPhone,         // 수신자 전화번호
    "callback" => $defaultCallback,   // 발신자 전화번호 
    "reqdate" => "",                  // 예약발송 기능("20191225100000" 형식), 비워두면 즉시발송
    "msg" => $talkMsg_fixDone,
    "template_code" => "CAPI008",     // 템플릿으로 등록 승인된 템플릿의 코드값
    "failed_type" => "SMS",
    "failed_subject" => "[캐치웰]",
    "failed_msg" => $smsMsg_fixDone,
    "btn_types" => "웹링크,배송조회",         // 카카오 알림톡 버튼타입     
    "btn_txts" => "AS 조회,배송조회",       // 카카오 알림톡 버튼이름
    "btn_urls1" => $asLink,
    "btn_urls2" => $asLink
];

if( $parcelNo == "" )
{
    // 접수완료 카톡알림
    $send_from = "접수";
    $response = $noti->postMessage( $msgBody_applicationDone );
    $resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
}
else
{
    // 출고완료 카톡알림
    $send_from = "출고";
    $response = $noti->postMessage( $msgBody_fixDone );
    $resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
}
*/
//20221122 bizppurio 알림톡 변경---------------------------------------------------------------------------------------------------------------------
//echo "parcelNo : ".$parcelNo."<br/>\n";

if( $parcelNo == "" )
{
    // 접수완료 카톡알림 //CAPI007
    $send_from = "접수_NEW";
    $defaultCallback = "07077776752";
    $DEBUG = 0;

    //$asLink = "http://backup.catchwell.com/cw_as/online_as/online_as_customer_search.php?searchBy=searchbyRegisterNo&searchData=".$regNumber;     
	$asLink = "https://csadmin.catchwell.com/cw_as_0924/pg_m/INImobile_mo_req.php?searchData=" . $regNumber . "&searchValuePhone=" . $customerPhone;     
	

    $headers[0]="Accept:application/json"; 
    $headers[1]="Content-Type:application/json"; 
    $headers[2]="Authorization:Bearer";
    $url = 'https://api.bizppurio.com/v3/message';
    //추가
    $token = getToken();
    //print_r( $token);
    $headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);

    //CAPI007-------------------------------------------------------
    $button_list1 = array("name"=>"가상계좌 발급", "type"=>"WL", "url_pc" =>$asLink, "url_mobile"=>$asLink);
    $button_list2 = array("name"=>"홈페이지", "type"=>"WL", "url_pc" =>"https://www.catchwell.com", "url_mobile"=>"https://www.catchwell.com");
    $buttons = array($button_list1,$button_list2);

    $at_CAPI007["message"] = "안녕하세요. 캐치웰입니다.\n\n"
    .$customerName." 님께서 신청하신 A/S 건이 접수되었습니다.\n".
	"제품 A/S를 위해 택배 수거를 요청하실 경우, 택배비가 선부과 되는 점 안내드립니다.\n\n".
    "* 접수번호 : ".$regNumber."\n".
    "* 제품명 : ".$modelName."\n".
    "* 고장증상 : ".$brokenType."\n\n".
    "가상계좌 발급 버튼을 눌러 계좌번호를 확인한 후, 편도 택배비 3,500원을 입금해 주세요.\n".
    "감사합니다.";

    $at_CAPI007["senderkey"] = "44970513f96ceef0a7b532fa874d4697ca936dce";
    $at_CAPI007["templatecode"] = "bizp_2025123115092820835616602";
    $at_CAPI007["button"] = $buttons;

    $content_CAPI007 = array("at" => $at_CAPI007);

    $data_CAPI007 = array( );
    $data_CAPI007["account"] = "catchwellota";//비즈뿌리오계정
    $data_CAPI007["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
    $data_CAPI007["type"] = "at";//메세지데이터타입
    $data_CAPI007["from"] = $defaultCallback;//발신번호
    $data_CAPI007["to"] = $customerPhone;//수신번호
    $data_CAPI007["content"] = $content_CAPI007;//메시지데이터

    $json_data = json_encode($data_CAPI007, JSON_UNESCAPED_SLASHES);
    if($DEBUG)
    {
    echo '<pre>';
    print_r($data_CAPI007);
    print_r($json_data);
    print_r(json_decode($json_data));
    echo '</pre>';
    }
    $Response_CAPI007 = httpsPost($url, $json_data, $headers1);

    $Ret_data       = (json_decode($Response_CAPI007));
    $code           = $Ret_data->code;// 결과 코드 code 1000이면 성공
    $description    = $Ret_data->description;//결과 메세지 success 출력
    $refkey_value   = $Ret_data->refkey;//고객사에서 부여한 키
    $messagekey     = $Ret_data->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 

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

    //$response = $noti->postMessage( $msgBody_applicationDone );//CAPI007
    //$resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
    $resultMsg = $description;
    $send_cmid = $messagekey;//"2022111714032455420102";
}
else
{
    // 출고완료 카톡알림 //CAPI008
    $send_from = "출고";

    $defaultCallback = "07077776752";
    $DEBUG = 0;

    $headers[0]="Accept:application/json"; 
    $headers[1]="Content-Type:application/json"; 
    $headers[2]="Authorization:Bearer";
    $url = 'https://api.bizppurio.com/v3/message';
    //추가
    $token = getToken();
    //print_r( $token);
    $headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);

    //CAPI008-------------------------------------------------------
    $button_list1_CAPI008 = array("name"=>"AS 조회", "type"=>"WL", "url_pc" =>"http://backup.catchwell.com/cw_as/online_as/online_as_customer_search.php?searchBy=searchbyRegisterNo&searchData=".$regNumber, "url_mobile"=>"http://backup.catchwell.com/cw_as/online_as/online_as_customer_search.php?searchBy=searchbyRegisterNo&searchData=".$regNumber);
    $button_list2_CAPI008 = array("name"=>"배송조회", "type"=>"DS");
    $buttons_CAPI008 = array($button_list1_CAPI008,$button_list2_CAPI008);

    $at_CAPI008["message"] = "안녕하세요. 캐치웰입니다.\n\n"
    .$customerName." 고객님이 접수하신 제품의 AS가 완료되어 금일 발송처리 되었습니다.\n\n".
    "* 접수번호 : ".$regNumber."\n".
    "* 제품명 : ".$modelName."\n".
    "* 고장증상 : ".$brokenType."\n".
    "* A/S내역 : ".$adminMemo."\n\n".
    "* 택배사 : ".$parcelCo."\n".
    "* 송장번호 : ".$parcelNo."\n\n".
    "관련 문의사항은 고객센터( $customerCenter )를 이용해 주세요.";

    $at_CAPI008["senderkey"] = "44970513f96ceef0a7b532fa874d4697ca936dce";
    $at_CAPI008["templatecode"] = "CAPI008";
    $at_CAPI008["button"] = $buttons_CAPI008;

    $content_CAPI008 = array("at" => $at_CAPI008);

    $data_CAPI008 = array( );
    $data_CAPI008["account"] = "catchwellota";//비즈뿌리오계정
    $data_CAPI008["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
    $data_CAPI008["type"] = "at";//메세지데이터타입
    $data_CAPI008["from"] = $defaultCallback;//발신번호
    $data_CAPI008["to"] = $customerPhone;//수신번호
    $data_CAPI008["content"] = $content_CAPI008;//메시지데이터

    $json_data = json_encode($data_CAPI008, JSON_UNESCAPED_SLASHES);
    if($DEBUG)
    {
    echo '<pre>';
    print_r($data_CAPI008);
    print_r($json_data);
    print_r(json_decode($json_data));
    echo '</pre>';
    }
    $Response_CAPI008 = httpsPost($url, $json_data, $headers1);

    $Ret_data1       = (json_decode($Response_CAPI008));
    $code1           = $Ret_data1->code;// 결과 코드 code 1000이면 성공
    $description1    = $Ret_data1->description;//결과 메세지 success 출력
    $refkey_value1   = $Ret_data1->refkey;//고객사에서 부여한 키
    $messagekey1     = $Ret_data1->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 

    if ($DEBUG)
    {
        echo '<pre>';
        print_r( "-------------------code1------------" );
        echo '<br>';
        print_r($code1);// 결과 코드 code 1000이면 성공
        echo '<br>';
        print_r($description1); //결과 메세지 success 출력
        echo '<br>';
        print_r($refkey_value1); //고객사에서 부여한 키
        echo '<br>';
        print_r($messagekey1);//메세지키 
        echo '<br>';
        echo '</pre>';
    }

    //$response = $noti->postMessage( $msgBody_fixDone );//CAPI008
    //$resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
    $resultMsg = $description1;
    $send_cmid = $messagekey1;//"2022111714032455420102";

}
//echo "<script>alert('{$resultMsg}'); history.back(-1);</script>"; //이미주석되어있음
//echo "<script>location.replace('$return_url');</script>";//이미주석되어있음
//--------------------------------------------------------------------------------------------------------------------------------------------------
// add cmid value to TB_REPORT_KAKAO(database)

//$send_date = $response->headers['Date'];
//$send_cmid = $response->body->cmid;
$send_date = date("Y-m-d H:i:s");
//$send_cmid = $messagekey;//"2022111714032455420102";

$db_name = "TB_REPORT_KAKAO";

if( $db->insert($db_name,
    "	SENDING='$send_from',
        DATE='$send_date',
        CMID='$send_cmid',
        STATUS='',
        REG_NO='$regNumber',
        PHONE='$customerPhone',
        CALLBACK='',
        RSLT='',
        MSG_RSLT='$customerName'
    "
)) 
{
    //echo "TB_REPORT_KAKAO successfully insert!!";
}
else
{
    //echo mysqli_error( $db );
}

//echo "<script>alert($return_url);</script>";
echo "<script>location.replace('$return_url');</script>";
?>