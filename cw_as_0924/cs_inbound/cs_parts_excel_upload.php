<?
include("../def_inc.php");
$mod	= M_INBOUND;	
$menu	= S_PARTS_LIST;
include("../header.php");



$db_name	= "cs_shipping_parts";
$return_url	= isset($_POST['return_url']) ? $_POST['return_url'] : ("cs_part_list.php");


//회수용 샘플:발송고객 일별 배달상세_202009231600852150.xlsx
//파일 업로드 경로
$file_dir	 = "../temp/";
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
		//$start_Row = 3;
		$start_Row = 2;//20230822

		//if ($objWorksheet->getCell('A3')->getValue()=='1' && $objWorksheet->getCell('A1')->getValue()=='NO') {
		//20230822 	
		//if ($objWorksheet->getCell('A2')->getValue()=='1' && $objWorksheet->getCell('A1')->getValue()=='NO') {
		if ($objWorksheet->getCell('A2')->getValue()=='1' && $objWorksheet->getCell('A1')->getValue()=='No') {
			//$start_Row = 3; 
			$start_Row = 2; //20230822
		} 
		else 
		{ 
			$tools->alertJavaGo("택배사 엑셀 파일을 확인하세요.(발송용 송장은 CJ택배를 이용합니다)", $return_url);

			$start_Row = 5;
		}
		
		for ($i = $start_Row ; $i <= $maxRow ; $i++) 
		{
			$noti_result = 0;
			
			if (true) {	
				//발송고객_일별_배달상세: B-운송장번호, F-송하인, G-받는분, H-전화번호, I-휴대번호, X-주문번호,  Y-제품명
				//20220105
				//$name = $objWorksheet->getCell('G' . $i)->getValue(); //발송용   받는분 
				$name = $objWorksheet->getCell('F' . $i)->getValue(); //발송용   받는분 //20230822

				$tracking_num = $objWorksheet->getCell('B' . $i)->getValue();//운송장번호
				$tracking_num = preg_replace("/[^0-9]/", "", $tracking_num);

				//$reg_num = $objWorksheet->getCell('X' . $i)->getValue(); //접수번호 주문번호 P211202-10
				$reg_num = $objWorksheet->getCell('AA' . $i)->getValue(); //접수번호 주문번호 P211202-10 //20230822 주문번호 
				 
				//$product = $objWorksheet->getCell('Y' . $i)->getValue(); //품명
				$product = $objWorksheet->getCell('AB' . $i)->getValue(); //품명

				//$phone = $objWorksheet->getCell('H' . $i)->getValue(); //전화번호 010-9160-****
				$phone = $objWorksheet->getCell('G' . $i)->getValue(); //전화번호 010-9160-**** //20230822

				//$mobile = $objWorksheet->getCell('I' . $i)->getValue(); //휴대번호 --****
				$mobile = $objWorksheet->getCell('H' . $i)->getValue(); //휴대번호 --****
				//$product = str_replace("부품","",$product);

				/* 이전파일
				$name = $objWorksheet->getCell('G' . $i)->getValue(); //발송용   받는분 

				$tracking_num = $objWorksheet->getCell('B' . $i)->getValue();//운송장번호
				$tracking_num = preg_replace("/[^0-9]/", "", $tracking_num);
				$reg_num = $objWorksheet->getCell('X' . $i)->getValue(); //접수번호 P211202-10
				$product = $objWorksheet->getCell('Y' . $i)->getValue(); //품명
				$phone = $objWorksheet->getCell('H' . $i)->getValue(); //전화번호 010-9160-****
				$mobile = $objWorksheet->getCell('I' . $i)->getValue(); //휴대번호 --****
				//$product = str_replace("부품","",$product);
				*/

				if ( ($reg_num !=="") && (strpos($reg_num, "P") === 0) )
				{
					$tmp = explode('-', $reg_num); //P211202-10
					if ($tmp[1] != "")
					{
						$idx = $tmp[1];
					}
				}
				else 
				{
//					continue;
				}
	
				if ($tracking_num == '') 
				{
					continue;
				}

				if ($idx == "") 
				{
					$where = "where customer_name='$name' AND status=0 AND (delivery_num='' OR delivery_num is NULL) ORDER BY idx DESC LIMIT 1";
				} 
				else 
				{
					$where = "where idx=$idx AND customer_name='$name' AND status=0 AND (delivery_num='' OR delivery_num is NULL) ORDER BY idx DESC LIMIT 1";
				}
//echo $where."<br>";
				if( $db->cnt($db_name, $where) > 0)
				{
					$data = "delivery_num='$tracking_num', " . "status=1"." ".$where;
					if( $db->update($db_name, $data) )
					{
						$cnt_suc++;

						if (true) 
						{
							//송장번호 입력시 알림톡전송
							if( $tracking_num != '' ) 
							{//운송장 번호가 있고, 처리중->처리완료로 상태 변경시 알림톡 발송 

								if ($idx == "") 
								{
									$where = "where customer_name='$name' AND delivery_num='$tracking_num' ORDER BY idx DESC LIMIT 1";
								} 
								else 
								{
									$where = "where idx='$idx' AND customer_name='$name' AND delivery_num='$tracking_num' ORDER BY idx DESC LIMIT 1";
								}

								$row = $db->object($db_name, $where);

								if ($row && true) //TEST
								{
									//카카오알림톡 전송 
									$parts = str_replace("(V)","",$row->parts_name.$row->parts_name_ex);  //20211213
									$parts = str_replace(";"," ",$parts);

									if ($reg_num==='') {
										$reg_num = sprintf("P%s-%d", date("ymd", strtotime($row->reg_datetime)), $row->idx); 
									}
									if ( $notiMsg->shipmentNotiMsg($db, $name, $row->customer_phone, $reg_num, $row->product_name."_".$parts, $tracking_num) ) 
									{
										//success
										$noti_cnt++;
										$noti_result = 1;
									}
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
	}
	catch (exception $e) {
		@unlink($file_pathname);
		$tools->alertJavaGo("파일을 읽는중 오류가 발생하였습니다.", $return_url);
	}



////////////////////////RETURN
	@unlink($_FILES['userfile']['tmp_name']);
	@unlink($file_pathname);

	$tools->alertJavaGo("업데이트 하였습니다.". "(업데이트:" . $cnt_suc. ")" . "(알림톡:" . $noti_cnt. ")", $return_url);
	
} else {
	$file_name 	= "";
	$tools->errMsg("파일을 확인하세요."); 
}

include('../footer.php');
?>