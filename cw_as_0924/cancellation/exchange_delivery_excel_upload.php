<?
//20230904 송장번호 업로드 새로 생성.

include("../def_inc.php");
include("cancellation_def.php");
$mod	= M_CANCELLATION;
//$menu	= S_CANCELLATION; 
$menu	= S_EXCHANGE;//20230515 
include("../header.php");

$db_name		= "cancellation_order";
$return_url     = "exchange_list.php";

//20230426 add
//샘플 : 배송리스트_event_YYYYMMDD.xlsx
//파일 업로드 경로

$file_dir	    = "files/";
$file_name      = $_FILES['userfile']['name'];
$file_pathname  = $file_dir.$file_name;

/*
//echo "file_dir : ".$file_dir."<br/>\n";
//echo "file_name : ".$file_name."<br/>\n";
echo "file_pathname : ".$file_pathname."<br/>\n";
echo "<script>alert('$file_pathname');</script>";
*/

//echo "file_dir : ".$file_dir."<br/>\n";
//echo "file_name : ".$file_name."<br/>\n";
echo "file_pathname : ".$file_pathname."<br/>\n";
echo "<script>alert('$file_pathname');</script>";

/*
if (USE_KAKAOTALK) {
	require("../kakao/CKakaoNotificationTalkEx.php");
	$notiMsg = new CKakaoNotificationTalkEx();
}
*/

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


    try
    {
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

         //20230904 재출고송장번호 업로드 
        /*
        A 발주날짜
        B 모델
        C 
        D 요청자(즉 담당자  )
        E 수량
        F idx로 넣었지만
        G 주문번호

        H 구매처
        //H 업체명 -> 구매처로 변경요청  20230913 

        I 수령자명
        J 재출고송장번호
        K 일반전화
        L 핸드폰
        M 주소
        N 배송메세지
        O 사방넷주문번호
        */
        //배송리스트_event_YYYYMMDD.xlsx

        $idx = 0;
        $cnt = 0;
		$noti_cnt = 0;
		$cnt_suc = 0;
		$start_Row = 2;
		$isCJ = 1;
        
        if ($objWorksheet->getCell('A1')->getValue()=='발주날짜') 
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
                    //20230809
                    배송리스트_event_YYYYMMDD.xlsx
                    //A 발주날짜, B 모델, C , D 요청자, E 수량, F idx, G 주문번호, H 업체명, I 수령자명, J 재출고송장번호, K 일반전화, L 핸드폰, M 주소, N 배송메세지, O 사방넷주문번호 
                    //20230913 업체명에서 구매처로 변경  
                      A 발주날짜, B 모델, C , D 요청자, E 수량, F idx, G 주문번호, H 구매처, I 수령자명, J 재출고송장번호, K 일반전화, L 핸드폰, M 주소, N 배송메세지, O 사방넷주문번호 
                    */
                    $ex_order_date                              = $objWorksheet->getCell('A' . $i)->getValue(); //발주날짜 
                    $ex_model_name                              = $objWorksheet->getCell('B' . $i)->getValue(); ////모델

                    $ex_admin_name                              = $objWorksheet->getCell('D' . $i)->getValue(); //요청자
                    $ex_amount                                  = $objWorksheet->getCell('E' . $i)->getValue(); //수량
                    $ex_idx                                     = $objWorksheet->getCell('F' . $i)->getValue(); //idx (접수번호)

                    $ex_order_id                                = $objWorksheet->getCell('G' . $i)->getValue(); ////주문번호

                    //$ex_company_name                            = $objWorksheet->getCell('H' . $i)->getValue(); ////업체명
                    //20230913 업체명 -> 구매처로 변경
                    $ex_shopping_mall                           = $objWorksheet->getCell('H' . $i)->getValue(); ////구매처

                    $ex_customer_name                           = $objWorksheet->getCell('I' . $i)->getValue(); ////수령자명

                    //$tracking                                 = $objWorksheet->getCell('J' . $i)->getValue(); //송장번호
                    $ex_exchange_tracking_number                = $objWorksheet->getCell('J' . $i)->getValue(); //재출고송장번호

                    $ex_phone1                                  = $objWorksheet->getCell('K' . $i)->getValue(); ////일반전화                    
                    $ex_phone2                                  = $objWorksheet->getCell('L' . $i)->getValue(); ////모바일전화
                    $ex_address                                 = $objWorksheet->getCell('M' . $i)->getValue(); ////주소

                    $ex_event_name                              = $objWorksheet->getCell('N' . $i)->getValue(); //배송메세지

                    $ex_order_id_sabangnet                      = $objWorksheet->getCell('O' . $i)->getValue(); ////사방넷주소록

                    /*
                    echo "excel ex_order_date: "                . $ex_order_date . "<br>";
                    echo "excel ex_model_name: "                . $ex_model_name . "<br>";
                    echo "excel ex_admin_name: "                . $ex_admin_name . "<br>";
					echo "excel ex_amount: "                    . $ex_amount . "<br>";
					echo "excel ex_idx: "                       . $ex_idx . "<br>";                   
                    echo "excel ex_order_id: "                  . $ex_order_id . "<br>";
					echo "excel ex_company_name: "              . $ex_company_name . "<br>";
					echo "excel ex_customer_name: "             . $ex_customer_name . "<br>";
                    echo "excel ex_exchange_tracking_number: "  . $ex_exchange_tracking_number . "<br>";   

					echo "excel ex_phone1: "                    . $ex_phone1 . "<br>";
                    echo "excel ex_phone2: "                    . $ex_phone2 . "<br>";

                    echo "excel ex_address: "                   . $ex_address . "<br>";
                    echo "excel ex_event_name: "                . $ex_event_name . "<br>";
                    echo "excel ex_order_id_sabangnet: "        . $ex_order_id_sabangnet . "<br>";
                    */

                    if ($ex_idx == '' || $ex_phone2 == '') 
                    {
                        //echo "<br>"."ex_idx : ". $ex_idx . "  ex_phone2 : ". $ex_phone2 . "<br>";//주석
                        continue;
                    }

                     //https://www.tcpschool.com/mysql/mysql_basic_select
                    //db 전체 검색하여 
                    //https://easy-coding.tistory.com/110  날짜
                    //$sql = "SELECT * FROM $db_name";//$sql = "SELECT * FROM $db_name where idx";
                    //$sql = "SELECT * FROM $db_name where date > '2023-05-01' ";//$sql = "SELECT * FROM $db_name where idx";
                    //$sql = "SELECT * FROM $db_name where date BETWEEN DATE_ADD(NOW(),INTERVAL -1 MONTH) AND NOW() ";
                    //$sql = "SELECT * FROM $db_name where date BETWEEN DATE_ADD(NOW(),INTERVAL -1 WEEK ) AND NOW() ";
                    //20230904 date 구매일에서 3달까지 조회되게 수정.
                    $sql = "SELECT * FROM $db_name where date BETWEEN DATE_ADD(NOW(),INTERVAL -3 MONTH ) AND NOW() ";
                    $result=mysqli_query($db->db_conn, $sql);
                    $row_cnt = mysqli_num_rows($result);//행갯수를 구한다 //중요
                    //echo  "row_cnt : " .  $row_cnt . "<br>";//주석

                    for ($j=0; $j<$row_cnt+1; $j++)//db 행갯수 만큼 돈다.
                    {
                        $row = mysqli_fetch_array($result);//중요
                         //echo "<br>". " j : ". $j . "<br>";

                         $date = $row['date'];//구매일추가
                         $idx = $row['idx'];
                         $name = $row['cusomer_name'];
                         $phone2 = $row['phone'];
                         $exchange_tracking_number = $row['exchange_tracking_number'];
                         $status = $row['status'];
                         $type = $row['type'];

                          //20230904
                        //echo "idx : ". $idx. " name : " . $name. " date : ".$date. " phone2 : ".$phone2. " exchange_tracking_number : " .$exchange_tracking_number."<br>";//주석

                        //if ($ex_idx == $idx || $ex_phone2 == $phone2)//엑셀에있는 idx와 db에 있는 idx가 같을 경우에만 실행
                        if ($ex_idx == $idx)//엑셀에있는 idx와 db에 있는 idx가 같을 경우에만 실행
                        {
                            //echo " ex_idx : " . $ex_idx. ", idx : ". $idx. ", ex_phone2 : " . $ex_phone2. ", phone2 : ". $phone2. "<br>";//주석
                            $cnt_suc++;

                            if( ($exchange_tracking_number == '') &&  ($status == 1) )  //재출고송장번호가 없고 상태값이 완료이면
                            {
                                
                                //echo " 0 exchange_tracking_number : " . $exchange_tracking_number. ", status : ". $status. ", type : ". $type."<br>";//주석
                                                                                         //업데이트시 엑셀에서 읽어온 값을 넣는다.
                                 $query = "update $db_name set exchange_tracking_number='$ex_exchange_tracking_number' where idx ='$idx'  ";

                                 if (mysqli_query($db->db_conn, $query))
                                 {
                                    //echo "update 성공"."<br>";//주석
                                 }
                                 else
                                 {
                                     $tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.(1)'); 
                                 }
                                 

                            }
                            else if(($exchange_tracking_number != '') && ($status==1))  //재출고송장번호가 있고 상태값이 완료이면 (재 출고 송장이 있어도 덮어쓰기 가능하게 추가)
                            {
                                //echo " 1 exchange_tracking_number : " . $exchange_tracking_number. ", status : ". $status. ", type : ". $type. "<br>";//주석

                                $query = "update $db_name set exchange_tracking_number='$ex_exchange_tracking_number' where idx ='$idx'  ";

                                if (mysqli_query($db->db_conn, $query))
                                {
                                   //echo "update 성공"."<br>";//주석
                                }
                                else
                                {
                                    $tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.(1)'); 
                                }
                                
                            }
                            else
                            {
                                //echo " 2 exchange_tracking_number : " . $exchange_tracking_number. ", status : ". $status. ", type : ". $type. "<br>";//주석
                            }

                        }
                        else
                        {
                            //echo " else ex_idx : " . $ex_idx. "idx : ". $idx."<br>";//주석
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
	//20230425 임시주석  업로드시 파일 삭제되는 부분 주석처리 -김민경
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