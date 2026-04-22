<?
include("../def_inc.php");
include("event_def.php");
$mod	= M_EVENT;
$menu	= S_EVENT; 
include("../header.php");

//20230619 만일 엑셀에 있는 헨드폰번호와 db에 있는 헨드폰 번호가 동일하면 실행되게 수정하기 

$db_name	= "cs_online_event";
$return_url	= "online_event.php";

//20230426 add
//샘플 : 포토이벤트 상품평취합_yyyymmdd.xlsx
//파일 업로드 경로
$file_dir	 = "files/";
//$file_name = $_FILES['userfile']['name'];
$file_name = $_FILES['userfile1']['name'];
$file_pathname = $file_dir.$file_name;

/*
//echo "file_dir : ".$file_dir."<br/>\n";
//echo "file_name : ".$file_name."<br/>\n";
echo "file_pathname : ".$file_pathname."<br/>\n";
echo "<script>alert('$file_pathname');</script>";
*/


/*
if (USE_KAKAOTALK) {
	require("../kakao/CKakaoNotificationTalkEx.php");
	$notiMsg = new CKakaoNotificationTalkEx();
}
*/

////////////////////////UPLOAD EXCEL
//if( $_FILES['userfile']['size'] > 0 ) {
if( $_FILES['userfile1']['size'] > 0 ) {
	
	$EXT_CHECK = array("php", "php3", "htm", "html", "cgi", "perl");	// 업로드 파일 제한 확장자 추가 가능
	//if( $EXT_TMP = explode( ".", $_FILES['userfile']['name'])) {	
    if( $EXT_TMP = explode( ".", $_FILES['userfile1']['name'])) {	  
		foreach ($EXT_CHECK as $value) { 
			if( strstr( $value, strtolower($EXT_TMP[1]))) { 
				$tools->errMsg( strtoupper($EXT_TMP[1])." 은 업로드 할수 없습니다." ); 
			} 
		}
	}
	//if( $_FILES['userfile']['size']  > 1024*1024*5) { 
    if( $_FILES['userfile1']['size']  > 1024*1024*5) { 
		$tools->errMsg("업로드 용량 초과입니다\\n\\n5메가 까지 업로드 가능합니다"); 
		exit(); 
	}
	
	//if( !@move_uploaded_file($_FILES['userfile']['tmp_name'], $file_pathname) ) { 
    if( !@move_uploaded_file($_FILES['userfile1']['tmp_name'], $file_pathname) ) { 
		//$tools->errMsg("파일 업로드 에러" . "--" . $_FILES['userfile']['tmp_name']); 
        $tools->errMsg("파일 업로드 에러" . "--" . $_FILES['userfile1']['tmp_name']); 
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
		$objReader = PHPExcel_IOFactory::createReaderForFile($file_pathname);// 엑셀 형식에 맞는 Reader 객체 생성

		$objReader->setReadDataOnly(true);// 읽기전용으로 설정

		$objExcel = $objReader->load($file_pathname);// 엑셀파일 읽기

		$objExcel->setActiveSheetIndex(0);// 첫번째 시트 선택

		$objWorksheet = $objExcel->getActiveSheet();

		$rowIterator = $objWorksheet->getRowIterator();

		foreach ($rowIterator as $rowi) {// 모든 행에 대해서

			$cellIterator = $rowi->getCellIterator();

			$cellIterator->setIterateOnlyExistingCells(false); 

		}

		$maxRow = $objWorksheet->getHighestRow();// 마지막 행
		$today = date("Y-m-d");
		
        /*
		//db update
		$idx = 0;
		$noti_cnt = 0;
		$cnt_suc = 0;
		$start_Row = 3;
		$isCJ = 1;

		//isCJ
		if ($objWorksheet->getCell('A3')->getValue()=='1' && $objWorksheet->getCell('A1')->getValue()=='NO') 
        {
			$isCJ = 1; $start_Row = 3; 
		} 
        else 
        { 
			$tools->alertJavaGo("택배사 엑셀 파일을 확인하세요.(발송용 송장은 CJ택배를 이용합니다)", $return_url);
		}
        */

         //20230426포토이벤트 상품평취합 db update
        //전화번호로 조회하여 DB에 저장
        //포토이벤트 상품평취합_2003년_4월.xlsx
        /*
        A 핀번호
        B 휴대폰번호
        C 상품명
        D 상품단가
        E 수량
        F 유효기간
        G MMS 발송일자 
        */
       
		$idx = 0;
        $cnt = 0;
		$noti_cnt = 0;
		$cnt_suc = 0;
		$start_Row = 2;
		$isCJ = 1;
        
        if ($objWorksheet->getCell('A1')->getValue()=='핀번호') 
        {
			$isCJ = 1; $start_Row = 2; //엑셀A2시작m
		} 
        else
        {
            $tools->alertJavaGo("엑셀 파일을 확인하세요.", $return_url);
        }

		for ($i = $start_Row ; $i <= $maxRow ; $i++) 
		{
            //echo "---------------------------------------------"."<br/>\n";
            //echo "i : ".$i." maxRow : ".$maxRow."<br/>\n";//주석

            if ($isCJ == 0) //우체국택배
			{
				//echo "0 isCJ : ".$isCJ."<br/>\n";
			}
            else
            {
                //echo "1 isCJ : ".$isCJ."<br/>\n";
                $noti_result = 0;

                if (true) 
                {	
                    /*
                    //20230426
                     포토이벤트 상품평취합_2003년_4월.xlsx
                     A 핀번호, B 휴대폰번호, C 상품명, D 상품단가, E 수량, F 유효기간,  G MMS 발송일자 
                    */
                    $e_pin_num        = $objWorksheet->getCell('A' . $i)->getValue(); //핀번호1
                    $e_hp             = $objWorksheet->getCell('B' . $i)->getValue(); //휴대폰번호2
                    $e_gift           = $objWorksheet->getCell('C' . $i)->getValue(); //상품명3
                    $e_gift_price     = $objWorksheet->getCell('D' . $i)->getValue(); //상품가격4
                    $e_gift_quantity  = $objWorksheet->getCell('E' . $i)->getValue(); //상품수량5
                    $e_expiry_date    = $objWorksheet->getCell('F' . $i)->getValue(); //유효기간6
                    $e_mms_senddate   = $objWorksheet->getCell('G' . $i)->getValue(); //mms발송일자7

                    $status = constant('STATUS_OK');//상태 

                   
                    //echo "status: ". $status." ,e_pin_num: " . $e_pin_num. " ,e_hp: ".$e_hp. " ,e_gift: ".$e_gift." ,e_mms_senddate: ".$e_mms_senddate."<br>";//주석
                    /*
                    echo "e_gift_price: ". $e_gift_price." ,e_gift_quantity: " . $e_gift_quantity. " ,e_expiry_date: ".$e_expiry_date. " ,e_mms_senddate: ".$e_mms_senddate."<br>";
                    echo "status: "                 . $status           . "<br>";
                    echo "excel e_pin_num: "        . $e_pin_num        . "<br>";
					echo "excel e_hp: "             . $e_hp             . "<br>";
					echo "excel e_gift: "           . $e_gift           . "<br>";                
                    echo "excel e_gift_price: "     . $e_gift_price     . "<br>";
					echo "excel e_gift_quantity: "  . $e_gift_quantity  . "<br>";
					echo "excel e_expiry_date: "    . $e_expiry_date    . "<br>";
                    echo "excel e_mms_senddate: "   . $e_mms_senddate   . "<br>";
                    */
                    if ($e_pin_num == '' || $e_hp == '') 
                    {
                        //echo "<br>"."e_pin_num : ". $e_pin_num . "  e_hp : ". $e_hp . "<br>";//주석
                        continue;
                    }

                    //20230426 파일 업로드 시 상태값 발송 완료되게 수정
                    if ($status==STATUS_NULL || STATUS_OK || STATUS_NOTOK)				$status=STATUS_DONE;
                    //echo "<br>"."status: ". $status . "<br>";

          
                    //https://www.tcpschool.com/mysql/mysql_basic_select
                    //db 전체 검색하여 
                    //https://easy-coding.tistory.com/110  날짜
                    //$sql = "SELECT * FROM $db_name";//$sql = "SELECT * FROM $db_name where hp";
                    //$sql = "SELECT * FROM $db_name where gdate > '2023-05-01' ";//$sql = "SELECT * FROM $db_name where hp";
                    //20230629 gdate 구매일에서 3달까지 조회되게 수정.
                    $sql = "SELECT * FROM $db_name where gdate BETWEEN DATE_ADD(NOW(),INTERVAL -3 MONTH) AND NOW() ";//$sql = "SELECT * FROM $db_name where hp";
                    
                    $result=mysqli_query($db->db_conn, $sql);
                    $row_cnt = mysqli_num_rows($result);//행갯수를 구한다 //중요
                    //echo  "row_cnt : " .  $row_cnt . "<br>";//주석
                    for ($j=0; $j<$row_cnt+1; $j++)//db 행갯수 만큼 돈다.
                    {
                        $row = mysqli_fetch_array($result);//중요
                        //echo "<br>". " j : ". $j . "<br>";
                        //echo " row['hp'] : " . $row['hp']. "<br>";

                        //echo "  j : " . $j . "  row[j] : " . $row[$j]."<br>";//$row[0] : 30942, $row[1] : 김민경 $row[2] : 010-3860-2779 
                        //echo  "  j : ". $j . "  row['hp'] : " . $row['hp']. "  hp: ". "$hp"."<br>";
                        //print_r($row);//https://extbrain.tistory.com/6   변수정보출력  주석처리

                        //var_dump($result->num_rows);
                        //echo "<br>"."인덱스로 접근 row[0]: " . $row[0] . "  row[1] :  ". $row[1] ." key값으로 접근 row['idx'] : " . $row['idx'] . " row['name'] : ". $row['name'] ."<br>";

                        $status_done = constant('STATUS_DONE');//20230426 업로드시 발송완료되게 수정

                        //https://zxchsr.tistory.com/36
                        //전화번호 특정문자 - 제거
                        $gdate = $row['gdate'];//구매일추가
                        $idx = $row['idx'];
                        $name = $row['name'];
                        $oid = $row['oid'];
                        $pin_num = $row['pin_num'];
                        $status = $row['status'];
                        $gift = $row['gift'];
                        //echo "idx : ". $idx. " name : " . $name. " oid : " . $oid. "pin_num : ". $pin_num. " status : " . $status. " gift : " . $gift. "<br>";//주석
                        //echo "idx : ". $idx. " name : " . $name. " oid : " . $oid. "pin_num : ". $pin_num. " status : " . $status. " gift : " . $gift." gdate : ".$gdate. "<br>";//주석
                        $hp = $row['hp'];
                        $hp = str_replace('-', '' , $hp);//db 핸드폰번호 - 뺀값 

                        $db_hp = $row['hp'];//db 헨드폰번호 그대로 값

                        //echo "0 e_hp : ". $e_hp. " row['hp'] : " . $row['hp']. " hp : " . $hp."<br>";//주석
                        //echo "0 e_hp : ". $e_hp. " hp : " . $hp. "<br>";
                        if ($e_hp == $hp)//엑셀에있는 전화번호와 db에 있는 전화번호가 같을 경우에만 실행
                        {
                            //echo "1 [e_hp==hp] e_hp : ". $e_hp. " hp : " . $hp. " db_hp". $db_hp. "idx : ". $idx ."gdate: ". $gdate."<br>";//주석
                            $cnt_suc++;

                            if(($pin_num == '') && ($status!=STATUS_DONE))  //핀번호가 없고 상태값이 완료가 아니면 
                            {
                                //echo "pin_num : ". $pin_num . "status : ". $status ." ,oid:".  $oid. "<br>";//주석
                                $query = "update $db_name set pin_num='$e_pin_num', gift='$e_gift', status='$status_done' where hp='$db_hp'  ";

                                //$data = " pin_num='$pin_num', gift='$gift',status=$status_done "." ".$where;//20230502 최종업데이트가 아니라 접수일이어서 제거
                                //$where = "where status='$status' AND pin_num='$pin_num' AND gift='$gift' ORDER BY hp DESC LIMIT 1";
                                //$query = "update $table set model_name='$model_name', box_size=$box_size, price=$price where type=$type and idx=$idx";
                               
                                if (mysqli_query($db->db_conn, $query))
                                {
                                   //echo "update 성공"."<br>";//주석
                                   
                                }
                                else
                                {
                                    $tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.(1)'); 
                                }
                            
                            }
                            else if(($pin_num =! '') && ($status==STATUS_DONE))//핀번호가 있고 상태값이 완료이면 
                            {
                                //echo "else pin_num : ". $pin_num . "status : ". $status ." ,oid:".  $oid. "<br>";
                                $query = "update $db_name set pin_num='$e_pin_num', gift='$e_gift', status='$status_done' where hp='$db_hp'  ";//이렇게 하면 중복
                                if (mysqli_query($db->db_conn, $query))
                                {
                                   //echo "update 성공"."<br>";
                                   
                                }
                                else
                                {
                                    $tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.(1)'); 
                                }

                            }
                            else
                            {
                                echo "else else "."<br>";
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
	//20230425 임시주석  업로드시 파일 삭제되는 부분 주석처리
	//@unlink($_FILES['userfile']['tmp_name']);
    //@unlink($_FILES['userfile1']['tmp_name']);//20230426 add
	//@unlink($file_pathname);


    //echo "<script>alert('$cnt_suc');</script>";//주석
    //echo "<br>". "cnt_suc : ". $cnt_suc . "<br>";
    
    //echo "<br>". "return_url : ". $return_url . "<br>";
    //echo "<script>alert('$return_url');</script>";

	$tools->alertJavaGo("업데이트 하였습니다.". "(업데이트:" . $cnt_suc. ")" , $return_url);
	
} else {
	$file_name 	= "";
	$tools->errMsg("파일을 확인하세요."); 
}

include('../footer.php');
?>