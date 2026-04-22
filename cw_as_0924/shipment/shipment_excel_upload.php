<?
error_reporting(E_ALL);

//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
ini_set('memory_limit','-1');

include("../def_inc.php");
$mod	= M_SHIPMENT;
$menu	= S_SHIPMENT_NEW; 
include("../header.php");


$return_url = $_POST['return_url'];
$table	= "shipping_date_new";

//2월 5일 배송리스트.xls
//파일 업로드 경로
$file_dir	 = "files/";
$file_name = "2022/".$_FILES['userfile1']['name'];
$file_pathname = $file_dir.$file_name;

//echo $file_pathname;
$cnt = 0;
$maxRow = 0;
////////////////////////UPLOAD EXCEL
if( $_FILES['userfile1']['size'] > 0 ) {
	
	$EXT_CHECK = array("php", "php3", "htm", "html", "cgi", "perl");	// 업로드 파일 제한 확장자 추가 가능
	if( $EXT_TMP = explode( ".", $_FILES['userfile1']['name'])) {	 
		foreach ($EXT_CHECK as $value) { 
			if( strstr( $value, strtolower($EXT_TMP[1]))) { 
				$tools->errMsg( strtoupper($EXT_TMP[1])." 은 업로드 할 수 없습니다." ); 
			} 
		}
	}
	
	if( $_FILES['userfile1']['size']  > 1024*1024*5) { 
		$tools->errMsg("업로드 용량 초과입니다\\n\\n5MB 까지 업로드 가능합니다"); 
		exit(); 
	}
	
	if( !@move_uploaded_file($_FILES['userfile1']['tmp_name'], $file_pathname) ) { 
		$tools->errMsg("파일 업로드 에러" . "--" . $_FILES['userfile1']['tmp_name']); 
		exit;
	}
	else { 

		//success
//		@unlink($_FILES['userfile1']['name']);
//		@unlink($_FILES['userfile1']['tmp_name']);
	} 
	
////////////////////////READ EXCEL 
	require_once "../PHPExcel/Classes/PHPExcel.php";
	require_once "../PHPExcel/Classes/PHPExcel/IOFactory.php";

	$objPHPExcel = new PHPExcel();

//발주날짜	모델	악세사리추가(수량으로 표기)		 	구매처(무시)	주문번호	업체명	수령자명	송장번호	일반전화	핸드폰	주소	배송메세지	사방넷 주문번호


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


		$hightestDataRow = $objWorksheet->getHighestDataRow();
		$arr_rows = array(2000, $hightestDataRow);
		$maxRow = max($arr_rows); //$objWorksheet->getHighestRow(); //20210907
		
		$today = date("Y-m-d");
		
		if ($objWorksheet->getCell('A1')->getValue()!='발주날짜') {
			$tools->errMsg("배송리스트 파일이 형식에 맞지 않습니다.");
			exit;
		}

//		if ($objWorksheet->getCell('A2')->getValue()=='') {
//			$tools->errMsg("배송리스트의 날짜가 없습니다.");
//			exit;
//		}
		
		//db update
		$idx = 0;
		$start_Row = 2;
		$cnt = 0;

		for ($i = $start_Row ; $i <= $maxRow ; $i++)
		{
			$date = $objWorksheet->getCell('A' . $i)->getValue();
			if ($date=="") {
				$date = date("Y-m-d");
			}
			else {
				$date = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($date)); 
			}

			if ($date!=$today) {
//				$tools->errMsg("배송리스트의 날짜가 오늘 날짜가 아닙니다.");
			}

			$model = $objWorksheet->getCell('B' . $i)->getValue();
			$orderid = $objWorksheet->getCell('G' . $i)->getValue(); //주문번호
			$orderid_sbn = $objWorksheet->getCell('O' . $i)->getValue(); //사방넷 주문번호
			$mall = $objWorksheet->getCell('H' . $i)->getValue();
			$name = $objWorksheet->getCell('I' . $i)->getValue();
			$phone1 = $objWorksheet->getCell('K' . $i)->getValue();//일반전화
			$phone2 = $objWorksheet->getCell('L' . $i)->getValue();//모바일전화
			$address = $objWorksheet->getCell('M' . $i)->getValue();//주소
			$deliverymemo = $objWorksheet->getCell('N' . $i)->getValue();//배송메시지

			if ($phone1=="" && $phone2=="") { //20220103
				$phone1="010-0000-0000";
				$phone2="010-0000-0000";
			}

			//20220111 전화번호 예외처리
			if ($phone1 != "" && strlen($phone1)>20 && strpos($phone1,'/')!==false)
			{//010-1111-2222/031-999-8888
				$arrPhone = explode("/", $phone1);
				$phone1 = $arrPhone[0];
			}
			if ($phone2 != "" && strlen($phone2)>20 && strpos($phone2,'/')!==false)
			{//010-1111-2222/031-999-8888
				$arrPhone = explode("/", $phone2);
				$phone2 = $arrPhone[0];
			}

			if ($name=="" || $address=="" || ($phone1=="" && $phone2=="") ) {
				break;
			}
			
			//insert 할때, 중복저장 방지 코드 추가 
			//$where = "where date='$date' and model='$model' and orderid='$orderid' and orderid_sabangnet='$orderid_sbn' and name='$name' ";
			//if ($db->object($table, $where) == NULL) 
			{

				if ($db->insert($table, "model='$model', date='$date', mall='$mall', orderid='$orderid', orderid_sabangnet='$orderid_sbn', name='$name', phone1='$phone1', phone2='$phone2', address='$address', deliverymemo='$deliverymemo', filename='$file_name' ") == true) {
					$cnt++;
				} else {
					$tools->alertJavaGo("데이터베이스 업데이트 에러가 발생하였습니다.", $return_url);
				}

			}
//			else 
			{
//				$test .= $name; 
//				$test .= "---"; 
			}

		}
		

	}
	catch (exception $e) {
		
		@unlink($file_pathname);
		$tools->alertJavaGo("파일을 읽는중 오류가 발생하였습니다.", $return_url);
	}

	//20210907 LOG추가 
	$db->insert("admin_log",
				"userid='$ADMIN_USERID', 
				contents='ship_excel_up', 
				ip='$_SERVER[REMOTE_ADDR]', 
				udate=now(), 
				comment='$file_name $hightestDataRow $maxRow $cnt $i' ");


////////////////////////RETURN
	@unlink($_FILES['userfile1']['tmp_name']);
//	@unlink($file_pathname);//엑셀파일 원본은 서버에 저장 

//	$tools->alertJavaGo("추가 하였습니다. "."(".$cnt."/".($maxRow-1).")", $return_url);
//	$tools->alertJavaGo($cnt . "개 추가 하였습니다. "."(".$cnt."/".($maxRow-1).")", $return_url);
	$tools->alertJavaGo($cnt . "개 추가 하였습니다. ", $return_url);

} else {

	$file_name 	= "";
	$tools->errMsg("파일을 확인하세요."); 
}

include('../footer.php');
?>