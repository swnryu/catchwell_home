<?
include("../def_inc.php");
include("event_def.php");
$mod	= M_EVENT;
$menu	= S_EVENT_COMMON; 
include("../header.php");


$db_name	= "lab_online_event";
$return_url	= "common_online_event.php";


//회수용 샘플:발송고객 일별 배달상세_202009231600852150.xlsx
//파일 업로드 경로
$file_dir	 = "temp/";
$file_name = $_FILES['userfile']['name'];
$file_pathname = $file_dir.$file_name;

if (1) {
	require("../kakao/CKakaoNotificationTalkEx.php");
	$notiMsg = new CKakaoNotificationTalkEx();
}

////////////////////////UPLOAD EXCEL
if( $_FILES['userfile']['size'] > 0 ) {
	
	$EXT_CHECK = array("php", "php3", "htm", "html", "cgi", "perl");	// 업로드 파일 제한 확장자 추가 가능
	if( $EXT_TMP = explode( ".", $_FILES['userfile']['name'])) {	 
		foreach ($EXT_CHECK as $value) { 
			if( strstr( $value, strtolower($EXT_TMP[1]))) { 
				$tools->errMsg( strtoupper($EXT_TMP[1])." 은 업로드 할수 없습니다." ); 
			} 
		}
	}
	if( $_FILES['userfile']['size']  > 1024*1024*5) { 
		$tools->errMsg("업로드 용량 초과입니다\\n\\n5메가 까지 업로드 가능합니다"); 
		exit(); 
	}
	
	if( !@move_uploaded_file($_FILES['userfile']['tmp_name'], $file_pathname) ) { 
		$tools->errMsg("파일 업로드 에러" . "--" . $_FILES['userfile']['tmp_name']); 
		exit;
	}
	else { 
		//success
//		@unlink($_FILES['userfile']['name']);
//		@unlink($_FILES['userfile']['tmp_name']);
	} 
	

////////////////////////READ EXCEL 
	require_once "../PHPExcel/Classes/PHPExcel.php";
	require_once "../PHPExcel/Classes/PHPExcel/IOFactory.php";

	$objPHPExcel = new PHPExcel();


//발송고객_일별_배달상세
//NO/B-운송장번호/C-접수일자/D-계약품목/E-송하인/F-받는분/G-전화번호/H-휴대번호/I-주소/J-인수자/집화사업담당/집화지점/집화점소/집화계획SM/집화일자/배달사업담당/배달지점/배달점소/배송계획SM/배달일자/미배달스캔일/미배달사유/W-주문번호/품명/단품코드

	try {
		$objReader = PHPExcel_IOFactory::createReaderForFile($file_pathname);

		$objReader->setReadDataOnly(true);

		$objExcel = $objReader->load($file_pathname);

		$objExcel->setActiveSheetIndex(0);

		$objWorksheet = $objExcel->getActiveSheet();

		$rowIterator = $objWorksheet->getRowIterator();

		foreach ($rowIterator as $rowi) {

			$cellIterator = $rowi->getCellIterator();

			$cellIterator->setIterateOnlyExistingCells(false); 

		}

		$maxRow = $objWorksheet->getHighestRow();
		$today = date("Y-m-d");
		
		//db update
		$idx = 0;
		$noti_cnt = 0;
		$cnt_suc = 0;
		$start_Row = 3;
		$isCJ = 1;

		//isCJ
		if ($objWorksheet->getCell('A3')->getValue()=='1' && $objWorksheet->getCell('A1')->getValue()=='NO') {
			$isCJ = 1; $start_Row = 3; 
		} else { 
			$tools->alertJavaGo("택배사 엑셀 파일을 확인하세요.(발송용 송장은 CJ택배를 이용합니다)", $return_url);
		}
		
		for ($i = $start_Row ; $i <= $maxRow ; $i++) 
		{
			$noti_result = 0;
			
			//발송고객_일별_배달상세: B-운송장번호, E-송하인, F-받는분, W-주문번호
			
			$name = $objWorksheet->getCell('F' . $i)->getValue(); //발송용
			$tracking_num = $objWorksheet->getCell('B' . $i)->getValue();//운송장번호
			$tracking_num = preg_replace("/[^0-9]/", "", $tracking_num);
			$oid = $objWorksheet->getCell('W' . $i)->getValue(); //주문번호
			$status = constant('STATUS_OK');

			//
			if ($tracking_num == '' || $oid == '') {
				continue;
			}

			if ($oid == "") {
				//$where = "where name='$name' AND status=$status AND (tracking_num='' OR tracking_num is NULL) ORDER BY idx DESC LIMIT 1";
				$where = "where customer_name='$name' AND status=0 ORDER BY idx DESC LIMIT 1";
			} else {
				//$where = "where name='$name' AND oid='$oid' AND status=$status AND (tracking_num='' OR tracking_num is NULL) ORDER BY idx DESC LIMIT 1";
				$where = "where customer_name='$name' AND status=0 and idx=$oid ORDER BY idx DESC LIMIT 1";
			}

			//echo $tracking_num . ""-----"" . $oid . "-----" . $name . "<br>";

			if( $db->cnt($db_name, $where) > 0 )
			{
				$data = "tracking_num='$tracking_num', status=1 ". " " . $where;
				if( $db->update($db_name, $data) )
				{
					$cnt_suc++;
/*
[캐치웰] 사은품 발송 안내
#{NAME} 고객님이 응모하신 #{EVENT} 이벤트 사은품이 금일 발송 처리 되었습니다.

■ 상품명 : #{MODEL}
■ 택배사 : #{DELIVERY_CO}
■ 송장번호 : #{DELIVERY_NO}

※ AS 및 기타 문의 사항은 고객센터(070-7777-6752)나 캐치웰 홈페이지를 이용해 주세요.

※ 홈페이지 회원에게만 드리는 다양한 할인 혜택도 함께 누리세요.

※ 항상 캐치웰을 이용해 주셔서 감사합니다.
*/

					if (0)
					{
						if($tracking_num != '') 
						{
							$where = "where customer_name='$name' AND idx=$oid";
							$row = $db->object($db_name, $where);

							if ($notiMsg->eventShipmentNotiMsg($db, $row->idx, $row->customer_name, $row->customer_phone, $row->event_name, $row->model_name.$row->gift, $tracking_num) ) 
							{
								//success
								$noti_cnt++;
								$noti_result = 1;
							}
						}
					}
				
				} 
				else 
				{
					$tools->alertJavaGo("데이터베이스 업데이트 에러가 발생하였습니다.", $return_url);
				}
			}
		}
	}
	catch (exception $e) {
		@unlink($file_pathname);
		$tools->alertJavaGo("파일을 읽는중 오류가 발생하였습니다.", $return_url);
	}


////////////////////////RETURN
	@unlink($_FILES['userfile']['tmp_name']);
	@unlink($file_pathname);

	$tools->alertJavaGo("업데이트 하였습니다.". "(업데이트:" . $cnt_suc. ")" , $return_url);
	
} else {
	$file_name 	= "";
	$tools->errMsg("파일을 확인하세요."); 
}

include('../footer.php');
?>