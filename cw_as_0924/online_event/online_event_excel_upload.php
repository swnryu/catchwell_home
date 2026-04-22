<?
include("../def_inc.php");
include("event_def.php");
$mod	= M_EVENT;
$menu	= S_EVENT; 
include("../header.php");


$db_name	= "cs_online_event";
$return_url	= "online_event.php";


//회수용 샘플:발송고객 일별 배달상세_202009231600852150.xlsx
//파일 업로드 경로
$file_dir	 = "files/";
$file_name = $_FILES['userfile']['name'];
$file_pathname = $file_dir.$file_name;

if (USE_KAKAOTALK) {
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

//기업고객_일별_명세_
//NO/B-운송장번호/C-송하인/D-받는분/E-전화번호/F-휴대번호/G-주소/현재상태/최종상품점소/처리시간/접수일자/집화일자/배달일/미배달스캔일/미배달사유/인수자/집화상위점소/집화점소/배달상위점소/배달점소/U-주문번호/품명/운임구분/박스타입/수량/금액/접수구분

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
			
			//20220105
			//발송고객_일별_배달상세: B-운송장번호, F-송하인, G-받는분, X-주문번호
			
			$name = $objWorksheet->getCell('G' . $i)->getValue(); //발송용 받는분
			$tracking_num = $objWorksheet->getCell('B' . $i)->getValue();//운송장번호
			$tracking_num = preg_replace("/[^0-9]/", "", $tracking_num);
			$oid = $objWorksheet->getCell('X' . $i)->getValue(); //주문번호
			$status = constant('STATUS_OK');

			if ($tracking_num == '') {
				continue;
			}

			if ($oid == "") {
				$where = "where name='$name' AND status=$status AND (tracking_num='' OR tracking_num is NULL) ORDER BY idx DESC LIMIT 1";
			} else {
				$where = "where name='$name' AND oid='$oid' AND status=$status AND (tracking_num='' OR tracking_num is NULL) ORDER BY idx DESC LIMIT 1";
			}

			if( $db->cnt($db_name, $where) > 0 )
			{
				$status_done = constant('STATUS_DONE');
				$data = "tracking_num='$tracking_num', status=$status_done "." ".$where;
				if( $db->update($db_name, $data) )
				{
					$cnt_suc++;
/*//not used
//이벤트응모 사은품 발송시, 알림톡 메시지 전송
					if (USE_KAKAOTALK)
					{
						if($tracking_num != '') 
						{
							$where = "where name='$name' AND process_state=$process_state";
							$row = $db->object($db_name, $where);

							if ($notiMsg->notiMsg($db, $row->customer_name, $row->customer_phone, $row->reg_num, 
													$row->product_name, $row->broken_type, $row->admin_memo, 
													$row->parcel_num_return, "") ) 
							{
								//success
								$noti_cnt++;
								$noti_result = 1;
							}
						}
					}
*/					
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