<?php
include("../common.php");


//*******************************************************************************
// FILE NAME : mx_rnoti.php
// FILE DESCRIPTION :
// 이니시스 smart phone 결제 결과 수신 페이지 샘플
// 기술문의 : ts@inicis.com
// HISTORY 
// 2010. 02. 25 최초작성 
// 2010  06. 23 WEB 방식의 가상계좌 사용시 가상계좌 채번 결과 무시 처리 추가(APP 방식은 해당 없음!!)
// WEB 방식일 경우 이미 P_NEXT_URL 에서 채번 결과를 전달 하였으므로, 
// 이니시스에서 전달하는 가상계좌 채번 결과 내용을 무시 하시기 바랍니다.
//*******************************************************************************

  $PGIP = $_SERVER['REMOTE_ADDR'];
  
  //if($PGIP == "203.238.37.15" || $PGIP == "118.129.210.25" || $PGIP == "183.109.71.153")	//PG에서 보냈는지 IP로 체크
  if(true)
  {

		// 이니시스 NOTI 서버에서 받은 Value
		$P_TID;				// 거래번호
		$P_MID;				// 상점아이디
		$P_AUTH_DT;			// 승인일자
		$P_STATUS;			// 거래상태 (00:성공, 01:실패)
		$P_TYPE;			// 지불수단
		$P_OID;				// 상점주문번호
		$P_FN_CD1;			// 금융사코드1
		$P_FN_CD2;			// 금융사코드2
		$P_FN_NM;			// 금융사명 (은행명, 카드사명, 이통사명)
		$P_AMT;				// 거래금액
		$P_UNAME;			// 결제고객성명
		$P_RMESG1;			// 결과코드
		$P_RMESG2;			// 결과메시지
		$P_NOTI;			// 노티메시지(상점에서 올린 메시지)
		$P_AUTH_NO;			// 승인번호
	

		$P_TID = $_REQUEST[P_TID];
		$P_MID = $_REQUEST[P_MID];
		$P_AUTH_DT = $_REQUEST[P_AUTH_DT];
		$P_STATUS = $_REQUEST[P_STATUS];
		$P_TYPE = $_REQUEST[P_TYPE];
		$P_OID = $_REQUEST[P_OID];
		$P_FN_CD1 = $_REQUEST[P_FN_CD1];
		$P_FN_CD2 = $_REQUEST[P_FN_CD2];
		$P_FN_NM = $_REQUEST[P_FN_NM];
		$P_AMT = $_REQUEST[P_AMT];
		$P_UNAME = $_REQUEST[P_UNAME];
		$P_RMESG1 = $_REQUEST[P_RMESG1];
		$P_RMESG2 = $_REQUEST[P_RMESG2];
		$P_NOTI = $_REQUEST[P_NOTI];
		$P_AUTH_NO = $_REQUEST[P_AUTH_NO];


		//WEB 방식의 경우 가상계좌 채번 결과 무시 처리
		//(APP 방식의 경우 해당 내용을 삭제 또는 주석 처리 하시기 바랍니다.)
		 if($P_TYPE == "VBANK")	//결제수단이 가상계좌이며
        	{
           	   if($P_STATUS != "02") //입금통보 "02" 가 아니면(가상계좌 채번 : 00 또는 01 경우)
           	   {
	              echo "OK";
        	      return;
           	   }
        	}



  		$PageCall_time = date("H:i:s");

		$value = array(
				"PageCall time" => $PageCall_time,
				"P_TID"			=> $P_TID,  
				"P_MID"     => $P_MID,  
				"P_AUTH_DT" => $P_AUTH_DT,      
				"P_STATUS"  => $P_STATUS,
				"P_TYPE"    => $P_TYPE,     
				"P_OID"     => $P_OID,  
				"P_FN_CD1"  => $P_FN_CD1,
				"P_FN_CD2"  => $P_FN_CD2,
				"P_FN_NM"   => $P_FN_NM,  
				"P_AMT"     => $P_AMT,  
				"P_UNAME"   => $P_UNAME,  
				"P_RMESG1"  => $P_RMESG1,  
				"P_RMESG2"  => $P_RMESG2,
				"P_NOTI"    => $P_NOTI,  
				"P_AUTH_NO" => $P_AUTH_NO
				);
		// 배열의 값들을 EUC-KR에서 UTF-8로 변환
		foreach ($value as $key => $val) {
			if (is_string($val)) { // 문자열인 경우만 변환
				$value[$key] = mb_convert_encoding($val, "UTF-8", "EUC-KR");
			}
}

 			// 결제처리에 관한 로그 기록
 		writeLog($value);
		
		$P_TID = mb_convert_encoding($P_TID, "UTF-8", "EUC-KR");
		$P_MID = mb_convert_encoding($P_MID, "UTF-8", "EUC-KR");
		$P_AUTH_DT = mb_convert_encoding($P_AUTH_DT, "UTF-8", "EUC-KR");
		$P_STATUS = mb_convert_encoding($P_STATUS, "UTF-8", "EUC-KR");
		$P_TYPE = mb_convert_encoding($P_TYPE, "UTF-8", "EUC-KR");
		$P_OID = mb_convert_encoding($P_OID, "UTF-8", "EUC-KR");
		$P_FN_CD1 = mb_convert_encoding($P_FN_CD1, "UTF-8", "EUC-KR");
		$P_FN_CD2 = mb_convert_encoding($P_FN_CD2, "UTF-8", "EUC-KR");
		$P_FN_NM = mb_convert_encoding($P_FN_NM, "UTF-8", "EUC-KR");
		$P_AMT = mb_convert_encoding($P_AMT, "UTF-8", "EUC-KR");
		$P_UNAME = mb_convert_encoding($P_UNAME, "UTF-8", "EUC-KR");
		$P_RMESG1 = mb_convert_encoding($P_RMESG1, "UTF-8", "EUC-KR");
		$P_RMESG2 = mb_convert_encoding($P_RMESG2, "UTF-8", "EUC-KR");
		$P_NOTI = mb_convert_encoding($P_NOTI, "UTF-8", "EUC-KR");
		$P_AUTH_NO = mb_convert_encoding($P_AUTH_NO, "UTF-8", "EUC-KR");
 
		$sql = "INSERT INTO TB_INICIS_NOTI (
            P_TID, 
            P_MID, 
            P_AUTH_DT, 
            P_STATUS, 
            P_TYPE, 
            P_OID, 
            P_FN_CD1, 
            P_FN_CD2, 
            P_FN_NM, 
            P_AMT, 
            P_UNAME, 
            P_RMESG1, 
            P_RMESG2, 
            P_NOTI, 
            P_AUTH_NO
        ) VALUES (
            '" . $P_TID . "', 
            '" . $P_MID . "', 
            '" . $P_AUTH_DT . "', 
            '" . $P_STATUS . "', 
            '" . $P_TYPE . "', 
            '" . $P_OID . "', 
            '" . $P_FN_CD1 . "', 
            '" . $P_FN_CD2 . "', 
            '" . $P_FN_NM . "', 
            '" . $P_AMT . "', 
            '" . $P_UNAME . "', 
            '" . $P_RMESG1 . "', 
            '" . $P_RMESG2 . "', 
            '" . $P_NOTI . "', 
            '" . $P_AUTH_NO . "'
        )";
		//writeLog($sql);
		$result = $db->result($sql);
		$update_sql = "UPDATE as_parcel_service SET process_state = '6' WHERE reg_num = '$P_OID'";//택배비 입금시 상태를 입금됨상태로 변경
		if ($db->result($update_sql)) {
			
			$row = $db->object("as_parcel_service","where reg_num='$P_OID'");
			$customer_phone = $row->customer_phone;
			require_once("../kakao/CKakaoNotificationTalkEx.php");
			//require_once 'INImobile_mo_return.php';
			$notiMsg = new CKakaoNotificationTalkEx();
			$notiMsg->NotiMsg_inicis_ok($db, $customer_phone, $P_OID);
			//echo $customer_phone;
			echo "OK";
			//exit;
		} else {
			echo "<script>alert('업데이트에 실패하였습니다. 다시 시도해주세요.');</script>";
		}
		/***********************************************************************************
		 ' 위에서 상점 데이터베이스에 등록 성공유무에 따라서 성공시에는 "OK"를 이니시스로 실패시는 "FAIL" 을
		 ' 리턴하셔야합니다. 아래 조건에 데이터베이스 성공시 받는 FLAG 변수를 넣으세요
		 ' (주의) OK를 리턴하지 않으시면 이니시스 지불 서버는 "OK"를 수신할때까지 계속 재전송을 시도합니다
		 ' 기타 다른 형태의 echo "" 는 하지 않으시기 바랍니다
		'***********************************************************************************/
	
		// if(데이터베이스 등록 성공 유무 조건변수 = true)
		    //echo "OK"; //절대로 지우지 마세요
		// else
		//	 echo "FAIL";

  }

function writeLog($msg)
{
    $file = "noti_input_".date("Ymd").".log";

    if(!($fp = fopen($path.$file, "a+"))) return 0;
                
    ob_start();
    print_r($msg);
    $ob_msg = ob_get_contents();
    ob_clean();
		
    if(fwrite($fp, " ".$ob_msg."\n") === FALSE)
    {
        fclose($fp);
        return 0;
    }
    fclose($fp);
    return 1;
}


?>
