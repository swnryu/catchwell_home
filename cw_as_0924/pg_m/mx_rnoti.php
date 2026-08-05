<?php
include("../common.php");
include("../def_inc.php");
include("../common_lib.php");


//*******************************************************************************
// FILE NAME : mx_rnoti.php
// FILE DESCRIPTION :
// �̴Ͻý� smart phone ���� ��� ���� ������ ����
// ������� : ts@inicis.com
// HISTORY 
// 2010. 02. 25 �����ۼ� 
// 2010  06. 23 WEB ����� ������� ���� ������� ä�� ��� ���� ó�� �߰�(APP ����� �ش� ����!!)
// WEB ����� ��� �̹� P_NEXT_URL ���� ä�� ����� ���� �Ͽ����Ƿ�, 
// �̴Ͻý����� �����ϴ� ������� ä�� ��� ������ ���� �Ͻñ� �ٶ��ϴ�.
//*******************************************************************************

  $PGIP = $_SERVER['REMOTE_ADDR'];
  
  //if($PGIP == "203.238.37.15" || $PGIP == "118.129.210.25" || $PGIP == "183.109.71.153")	//PG���� ���´��� IP�� üũ
  if(true)
  {

		// �̴Ͻý� NOTI �������� ���� Value
		$P_TID;				// �ŷ���ȣ
		$P_MID;				// �������̵�
		$P_AUTH_DT;			// ��������
		$P_STATUS;			// �ŷ����� (00:����, 01:����)
		$P_TYPE;			// ���Ҽ���
		$P_OID;				// �����ֹ���ȣ
		$P_FN_CD1;			// �������ڵ�1
		$P_FN_CD2;			// �������ڵ�2
		$P_FN_NM;			// ������� (�����, ī����, ������)
		$P_AMT;				// �ŷ��ݾ�
		$P_UNAME;			// ������������
		$P_RMESG1;			// ����ڵ�
		$P_RMESG2;			// ����޽���
		$P_NOTI;			// ��Ƽ�޽���(�������� �ø� �޽���)
		$P_AUTH_NO;			// ���ι�ȣ
	

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


		//WEB ����� ��� ������� ä�� ��� ���� ó��
		//(APP ����� ��� �ش� ������ ���� �Ǵ� �ּ� ó�� �Ͻñ� �ٶ��ϴ�.)
		 if($P_TYPE == "VBANK")	//���������� ��������̸�
        	{
           	   if($P_STATUS != "02") //�Ա��뺸 "02" �� �ƴϸ�(������� ä�� : 00 �Ǵ� 01 ���)
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
		// �迭�� ������ EUC-KR���� UTF-8�� ��ȯ
		foreach ($value as $key => $val) {
			if (is_string($val)) { // ���ڿ��� ��츸 ��ȯ
				$value[$key] = mb_convert_encoding($val, "UTF-8", "EUC-KR");
			}
}

 			// ����ó���� ���� �α� ���
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

		if (substr($P_OID, -2) === '_R') {
			// 수리비 입금 처리 (P_OID = reg_num + "_R")
			$reg_num    = substr($P_OID, 0, -2);
			$row_before = $db->object("as_parcel_service", "where reg_num='$reg_num'");
			$update_sql = "UPDATE as_parcel_service SET process_state = '9' WHERE reg_num = '$reg_num'";
			if ($db->result($update_sql)) {
				if ($row_before) {
					$prev = (int)$row_before->process_state;
					$db->insert("as_process_history", "as_idx={$row_before->idx}, reg_num='$reg_num', prev_state=$prev, new_state=9, changed_by='이니시스(수리비결제)', changed_at=now()");
				}
				$row           = $db->object("as_parcel_service", "where reg_num='$reg_num'");
				$customer_phone = $row->customer_phone;
				require_once("../kakao/CKakaoNotificationTalkEx.php");
				$notiMsg = new CKakaoNotificationTalkEx();
				$notiMsg->NotiMsg_repair_paid_ok($db, $customer_phone, $reg_num, $P_AMT, $row->pic_name);

				$flow_msg = "[수리비 입금완료]\n"
				          . "▸ 접수번호: {$reg_num}\n"
				          . "▸ 고객명: {$row->customer_name}\n"
				          . "▸ 입금액: " . number_format($P_AMT) . "원\n"
				          . "▸ 담당자: {$row->pic_name}\n"
				          . "▸ 시간: " . date("Y-m-d H:i:s");
				sendFlowMessage(4186691, $flow_msg);

				echo "OK";
			} else {
				echo "FAIL";
			}
		} else {
			// 기존 회수 택배비 처리
			$row_before = $db->object("as_parcel_service","where reg_num='$P_OID'");
			$update_sql = "UPDATE as_parcel_service SET process_state = '6' WHERE reg_num = '$P_OID'";//수리비 입금시 상태를 입금됨상태로 변경
			if ($db->result($update_sql)) {
				// 히스토리 추가 (이니시스 자동결제 입금 후 상태변경)
				if ($row_before) {
					$prev = (int)$row_before->process_state;
					$db->insert("as_process_history", "as_idx={$row_before->idx}, reg_num='$P_OID', prev_state=$prev, new_state=6, changed_by='이니시스(자동결제)', changed_at=now()");
				}
				$row = $db->object("as_parcel_service","where reg_num='$P_OID'");
				$customer_phone = $row->customer_phone;
				require_once("../kakao/CKakaoNotificationTalkEx.php");
				$notiMsg = new CKakaoNotificationTalkEx();
				$notiMsg->NotiMsg_inicis_ok($db, $customer_phone, $P_OID);
				echo "OK";
			} else {
				echo "<script>alert('업데이트에 실패하였습니다. 다시 시도해주세요.');</script>";
			}
		}
		/***********************************************************************************
		 ' ������ ���� �����ͺ��̽��� ��� ���������� ���� �����ÿ��� "OK"�� �̴Ͻý��� ���нô� "FAIL" ��
		 ' �����ϼž��մϴ�. �Ʒ� ���ǿ� �����ͺ��̽� ������ �޴� FLAG ������ ��������
		 ' (����) OK�� �������� �����ø� �̴Ͻý� ���� ������ "OK"�� �����Ҷ����� ��� �������� �õ��մϴ�
		 ' ��Ÿ �ٸ� ������ echo "" �� ���� �����ñ� �ٶ��ϴ�
		'***********************************************************************************/
	
		// if(�����ͺ��̽� ��� ���� ���� ���Ǻ��� = true)
		    //echo "OK"; //����� ������ ������
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
