<?
include("../def_inc.php");
$mod	= M_AS;	
$menu	= isset($_GET['from'])?$_GET['from']:S_AS_REGISTERING;
include("../header.php");



$db_name	= "as_parcel_service";
$return_url	= isset($_POST['return_url']) ? $_POST['return_url'] : ("online_as.php?state=$state");
$state = isset($_POST['state'])?$_POST['state']:0;

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
		//$start_Row = 3;
		$start_Row = 2;//20230822
		$isCJ = 1;

		//isCJ
		//if ($objWorksheet->getCell('A3')->getValue()=='1' && $objWorksheet->getCell('A1')->getValue()=='NO') {
		//20230822
		//if ($objWorksheet->getCell('A2')->getValue()=='1' && $objWorksheet->getCell('A1')->getValue()=='NO') {
		if ($objWorksheet->getCell('A2')->getValue()=='1' && $objWorksheet->getCell('A1')->getValue()=='No') {
			//$isCJ = 1; $start_Row = 3; 
			$isCJ = 1; $start_Row = 2; //20230822 엑셀 A2시작
		} 
		else 
		{ 
			if ($state==ST_FIX_DONE) {
				//error 
				$tools->alertJavaGo("택배사 엑셀 파일을 확인하세요.(발송용 송장은 CJ택배를 이용합니다)", $return_url);
			}
			$isCJ = 0; $start_Row = 5; //$start_Row = 9;
		}

		//$isCJ = 1; $start_Row = 3; //test
		$isCJ = 1; $start_Row = 2; //test 20230822
		for ($i = $start_Row ; $i <= $maxRow ; $i++) 
		{
			if ($isCJ == 0) 
			{ //우체국택배
				$noti_result = 0;
				
				if ($state==ST_FIX_DONE) { 
					$name = $objWorksheet->getCell('H' . $i)->getValue(); //발송용
				} else {
					//$name = $objWorksheet->getCell('H' . $i)->getValue(); //회수용
					$name = $objWorksheet->getCell('K' . $i)->getValue(); //회수용
				}

				//$tracking_num = $objWorksheet->getCell('F' . $i)->getValue();//운송장번호
				$tracking_num = $objWorksheet->getCell('A' . $i)->getValue();//운송장번호
				$tracking_num = preg_replace("/[^0-9]/", "", $tracking_num);
				//$reg_num = $objWorksheet->getCell('E' . $i)->getValue(); //접수번호
				$reg_num = $objWorksheet->getCell('AA' . $i)->getValue(); //접수번호

				if ($state==ST_REGISTERING || $state==ST_DC)				$process_state=ST_REG_DONE;
				else if ($state==ST_REG_DONE)			$process_state=ST_REG_DONE;
				else if ($state==ST_FIX_DONE)			$process_state=ST_AS_COMPLETED;
			
				//$where = "where customer_name='$name' AND customer_phone like '%$phone%' AND customer_addr like '%$addr%' AND reg_num='$reg_num'"; //상세주소로검색
				if ($reg_num == "") {
					$where = "where customer_name='$name' AND process_state=$state ORDER BY reg_date DESC LIMIT 1";
				} else {
					$where = "where reg_num='$reg_num' AND process_state=$state ORDER BY reg_date DESC LIMIT 1";
				}

				if( $db->cnt($db_name, $where) > 0)
				{
					if ($state==ST_REGISTERING || $state==ST_DC || $state==ST_REG_DONE) //회수용 송장 처리 
					{
						$data = "update_time=now(), parcel_num='$tracking_num', " . "process_state=$process_state"." ".$where;
						if( $db->update($db_name, $data))
						{
							$cnt_suc++;

						} else {
							$tools->alertJavaGo("데이터베이스 업데이트 에러가 발생하였습니다.", $return_url);
						}
					}
					else if ($state==ST_FIX_DONE) //발송용 송장 처리 
					{
						$data = "update_time=now(), parcel_num_return='$tracking_num', " . "process_state=$process_state"." ".$where;
						if( $db->update($db_name, $data) )
						{
							$cnt_suc++;

							if (true) 
							{
								//수리완료에서, 송장번호 입력시 알림톡전송
								//[출고완료] 카카오알림톡전송
								if( ($tracking_num != '') && 
									($process_state==ST_AS_COMPLETED) && ($state!=ST_AS_COMPLETED) ) 
								{//운송장 번호가 있고, 수리완료->출고로 상태 변경시 알림톡 발송 
									//$where = "where reg_num='$reg_num'";
									$where = "where customer_name='$name' AND process_state=$process_state";
									$row = $db->object($db_name, $where);

									$broken_type_desc = mb_strimwidth($row->customer_desc, 0, 60, '...', 'UTF-8'); //2021-10-15

									if ($notiMsg->notiMsg($db,	$row->customer_name, $row->customer_phone, $row->reg_num, 
															$row->product_name, $broken_type_desc, $row->admin_memo, 
															$tracking_num, "") ) 
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
			else 
			{ //CJ 택배
				$noti_result = 0;
				
				if (true) 
				{	

					//기업고객_일별_명세    : B-운송장번호, C-송하인, D-받는분, U-주문번호
					//발송고객_일별_배달상세: B-운송장번호, E-송하인, F-받는분, W-주문번호,  X-제품명
					//20220105
					if ($state==ST_FIX_DONE) //수리완료
					{ 
						//$name = $objWorksheet->getCell('G' . $i)->getValue(); //발송용   20230822 발송고객_일별_배달상세 이전 파일 G: 받는분
						$name = $objWorksheet->getCell('F' . $i)->getValue();  //발송용   20230822 발송고객_일별_배달상세 현재 파일 F: 받는분
					} 
					else 
					{
						//$name = $objWorksheet->getCell('F' . $i)->getValue(); //회수용   20230822 발송고객_일별_배달상세 이전 파일 F: 송하인
						$name = $objWorksheet->getCell('E' . $i)->getValue(); //회수용   20230822 발송고객_일별_배달상세 이전 파일 E: 송하인
					}

					$tracking_num = $objWorksheet->getCell('B' . $i)->getValue();//운송장번호
					$tracking_num = preg_replace("/[^0-9]/", "", $tracking_num);

					//$reg_num = $objWorksheet->getCell('X' . $i)->getValue(); //접수번호/주문번호
					$reg_num = $objWorksheet->getCell('AE' . $i)->getValue(); //접수번호/주문번호 20230822 
					
					//echo $state . "<br>";
					//echo $tracking_num . "<br>";
					//echo $reg_num . "<br>";
					//echo $where . "<br>";

					if ($tracking_num == '' && $state==ST_REG_DONE) {
						continue;
					}

					if ($state==ST_REGISTERING || $state==ST_DC)				$process_state=ST_REG_DONE;
					else if ($state==ST_REG_DONE)			$process_state=ST_REG_DONE;
					else if ($state==ST_FIX_DONE)			$process_state=ST_AS_COMPLETED;
					
					
					//$where = "where customer_name='$name' AND customer_phone like '%$phone%' AND customer_addr like '%$addr%' AND reg_num='$reg_num'"; //상세주소로검색
					if ($reg_num == "") 
					{
						if ($state==ST_REGISTERING || $state==ST_DC || $state==ST_REG_DONE) //회수용 송장 처리 
						{
							$where = "where customer_name='$name' AND process_state=$state AND (parcel_num='' OR parcel_num is NULL) ORDER BY reg_date DESC LIMIT 1";
						} 
						else if ($state==ST_FIX_DONE) //발송용 송장 처리 
						{
							$where = "where customer_name='$name' AND process_state=$state AND (parcel_num_return='' OR parcel_num_return is NULL) ORDER BY reg_date DESC LIMIT 1";
						}
					} 
					else 
					{
						if ($state==ST_REGISTERING || $state==ST_DC || $state==ST_REG_DONE) //회수용 송장 처리 
						{
							$where = "where reg_num='$reg_num' AND process_state=$state AND (parcel_num='' OR parcel_num is NULL) ORDER BY reg_date DESC LIMIT 1";
						} 
						else if ($state==ST_FIX_DONE) //발송용 송장 처리 
						{
							$where = "where reg_num='$reg_num' AND process_state=$state AND (parcel_num_return='' OR parcel_num_return is NULL) ORDER BY reg_date DESC LIMIT 1";
						}
					}
					

					if( $db->cnt($db_name, $where) > 0)
					{
						if ($state==ST_REGISTERING || $state==ST_DC || $state==ST_REG_DONE) //회수용 송장 처리 
						{
							$data = "update_time=now(), parcel_num='$tracking_num', " . "process_state=$process_state"." ".$where;
							if( $db->update($db_name, $data))
							{
								$cnt_suc++;

							} else {
								$tools->alertJavaGo("데이터베이스 업데이트 에러가 발생하였습니다.", $return_url);
							}
						}
						else if ($state==ST_FIX_DONE) //발송용 송장 처리 
						{
							$data = "update_time=now(), parcel_num_return='$tracking_num', " . "process_state=$process_state"." ".$where;
							if( $db->update($db_name, $data) )
							{
								$cnt_suc++;

								if (true) 
								{
									//수리완료에서, 송장번호 입력시 알림톡전송
									//[출고완료] 카카오알림톡전송
									if( ($tracking_num != '') && 
										($process_state==ST_AS_COMPLETED) && ($state!=ST_AS_COMPLETED) ) 
									{//운송장 번호가 있고, 수리완료->출고로 상태 변경시 알림톡 발송 
										//$where = "where customer_name='$name' AND process_state=$process_state AND parcel_num_return='$tracking_num' ";

										if ($reg_num == "") {
											$where = "where customer_name='$name' AND process_state=$process_state  AND parcel_num_return='$tracking_num' ORDER BY reg_date DESC LIMIT 1";
										} else {
											$where = "where reg_num='$reg_num' AND customer_name='$name' AND process_state=$process_state  AND parcel_num_return='$tracking_num' ORDER BY reg_date DESC LIMIT 1";
										}

										//$where = "where customer_name='$name' AND process_state=$process_state";
										$row = $db->object($db_name, $where);

										$memo = $row->admin_memo;
										$memo = str_replace("(V)","",$memo);
										$memo = str_replace("(R)","",$memo);
										$memo = str_replace("(H)","",$memo);
										$memo = str_replace("(S)","",$memo);
										$memo = str_replace("(M)","",$memo);
										$memo = str_replace("[ETC]","",$memo);
										
										$broken_type_desc = mb_strimwidth($row->customer_desc, 0, 60, '...', 'UTF-8'); //2021-10-15

										if ($notiMsg->notiMsg_new($db, $row->customer_name, $row->customer_phone, $row->reg_num, 
																$row->product_name, $broken_type_desc, $memo, 
																$row->parcel_num_return, "",$row->pic_name) ) 
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
//	$tools->alertJavaGo("업데이트 하였습니다.". "(" . $cnt_suc. ")", $return_url);
	$tools->alertJavaGo("업데이트 하였습니다.". "(업데이트:" . $cnt_suc. ")" . "(알림톡:" . $noti_cnt. ")", $return_url);
	
} else {
	$file_name 	= "";
	$tools->errMsg("파일을 확인하세요."); 
}

include('../footer.php');
?>