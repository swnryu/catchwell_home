<?
include("../def_inc.php");
$mod	= M_SHIPMENT;
$menu	= S_SHIPMENT_NEW; 
include("../header.php");



$db_name	= "shipping_date_new";
$return_url	= $_POST['return_url'];
$send_noti	= isset($_POST['send_noti']) ? $_POST['send_noti']: 0; //20211216



//회수용 샘플:발송고객 일별 배달상세_202009231600852150.xlsx
//파일 업로드 경로
$file_dir	 = "files/";
$file_name = $_FILES['userfile']['name'];
$file_pathname = $file_dir.$file_name;

require("../kakao/CKakaoNotificationTalkEx.php");
$notiMsg = new CKakaoNotificationTalkEx();

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
		$tools->errMsg("업로드 용량 초과입니다\\n\\n5MB 까지 업로드 가능합니다"); 
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

		if ($objWorksheet->getCell('A1')->getValue()!='No') {
			$tools->errMsg("발송고객 일별 배달 상세 파일이 형식에 맞지 않습니다.");
			exit;
		}

		$maxRow = $objWorksheet->getHighestRow();
		$today = date("Y-m-d");
		
		//db update
		$idx = 0;
		$noti_cnt = 0;
		$cnt_suc = 0;
		$start_Row = 2;

		if ($objWorksheet->getCell('A2')->getValue()=='1' && $objWorksheet->getCell('A1')->getValue()=='No') {
			$start_Row = 2; 
		} 
		//20220105
		$col_name = "U";//받는분

		$col_oid = "S";//주문번호

		$col_product = "Z";//상품명

		for ($i = $start_Row ; $i <= $maxRow ; $i++) 
		{
			$noti_result = 0;
			
			if (true) {	
				
				$no = $objWorksheet->getCell('A' . $i)->getValue();//NO
				if ($no=="") {
					break;
				}

				$date = $objWorksheet->getCell('G' . $i)->getValue();//집화예정일자
				//$date = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($date)); 
				$tracking = $objWorksheet->getCell('H' . $i)->getValue();//운송장번호
				$tracking = preg_replace("/[^0-9]/", "", $tracking);
				$name = $objWorksheet->getCell($col_name . $i)->getValue(); //받는사람 이름
				$orderid = $objWorksheet->getCell($col_oid . $i)->getValue(); //주문번호
				$model = $objWorksheet->getCell($col_product . $i)->getValue(); //제품명

				if ($tracking=="") {
					continue;
				}
//				if( $row = $db->object($db_name, "where name='$name' AND orderid_sabangnet='$orderid' AND date='$date' AND model='$model' AND status=0 AND tracking is null ORDER BY idx DESC LIMIT 1") )
				if( $row = $db->object($db_name, "where name='$name' AND orderid_sabangnet='$orderid' AND date='$date' AND model='$model' AND status=0 ORDER BY idx DESC LIMIT 1") )
				{
					if ($row==NULL || $row->name==NULL || $row->name=="") {
						continue;
					}
					$phone = $row->phone2;
					if ($phone=="" || $phone==NULL) {
						$phone = $row->phone1;
					}

					//$data = "tracking='$tracking' where name='$name' AND orderid='$orderid' AND date='$date' ";
					$data = "tracking='$tracking', status=1 where idx=$row->idx ";
					
					if( $db->update($db_name, $data) )
					{
						$cnt_suc++;
						//echo $cnt_suc;

						//echo $no . ">>>>>" . $cnt_suc."<br>";

						if ( $send_noti ) //20211216
						{
							//카카오알림톡 전송 
							if ( $notiMsg->shipmentNotiMsg_CAPI014($db, $phone, $orderid, $tracking) ) 
							{
								//success
								$noti_cnt++;
								$noti_result = 1;
							}

						}
					}
					else 
					{
						$tools->alertJavaGo("데이터베이스 업데이트 에러가 발생하였습니다.", $return_url);
					}
					
					//echo $data."<br>";
				}
				//echo $no."<br>";
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

	$tools->alertJavaGo("업데이트 하였습니다.". "(업데이트:" . $cnt_suc. ")" . "(알림톡:" . $noti_cnt. ")", $return_url); //20211216
//	$tools->alertJavaGo("업데이트 하였습니다.". "(업데이트:" . $cnt_suc. ")", $return_url);
	
} else {
	$file_name 	= "";
	$tools->errMsg("파일을 확인하세요."); 
}

include('../footer.php');
?>