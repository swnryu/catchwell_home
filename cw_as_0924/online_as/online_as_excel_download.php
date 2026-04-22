<?//session_name("CW_AS");
session_start();

include ("../common.php");
require ("../check_session.php");
include ("../def_inc.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';

const FORMAT_NUMBER_COMMA_SEPARATED3 = '#,##0';
const FORMAT_CODE_ACCOUNTING = '_-* #,##0_-;-* #,##0_-;_-* "-"_-;_-@_-';


$state = isset($_GET['state']) ? $_GET['state'] : 0;

$isReport = isset($_GET['query_where']);


// create new PHPExcel object
$objPHPExcel = new PHPExcel();

$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);

//$objPHPExcel->getActiveSheet()->getStyle("A1:G1")->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(40);


// Add data
if ($isReport) {
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);

    $objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', '접수번호')
    ->setCellValue('B1', '상태') 
    ->setCellValue('C1', '이름')
    ->setCellValue('D1', '전화번호') //필드 추가
    ->setCellValue('E1', '모델명')
    ->setCellValue('F1', '구매일')
    ->setCellValue('G1', '불량유형')
    ->setCellValue('H1', '불량내용')
    ->setCellValue('I1', '조치사항')
    ->setCellValue('J1', '운송장번호')
    ->setCellValue('K1', '유상수리비용');//20210105

    $objPHPExcel->getActiveSheet()
    ->getStyle('A1:K1')
    ->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('FFd0d0d0');//20210105
    
}
else {
    if ($state==ST_FIX_DONE) { //발송용 송장 
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '품목명')
        ->setCellValue('B1', '박스수량')
        ->setCellValue('C1', '이름')
        ->setCellValue('D1', '전화번호')
        ->setCellValue('E1', '전화번호2')
        ->setCellValue('F1', '주소')
        ->setCellValue('G1', '배송메시지')
        //->setCellValue('H1', '운송장번호')
        ->setCellValue('H1', '고객주문번호');   

        $objPHPExcel->getActiveSheet()
        ->getStyle('A1:H1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('FFd0d0d0');
    
    } 
    else { //회수용 송장 
        if (USE_DELIVERY_EPOST) {
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);

            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '발송인명')
            ->setCellValue('B1', '우편번호')
            ->setCellValue('C1', '발송인우편번호주소')
            ->setCellValue('D1', '발송인상세주소')
            ->setCellValue('E1', '발송인전화')
            ->setCellValue('F1', '발송인이동통신')
            ->setCellValue('G1', '상품명')
            ->setCellValue('H1', '상품코드')
            ->setCellValue('I1', '상품모델')
            ->setCellValue('J1', '사이즈')
            ->setCellValue('K1', '색상')
            ->setCellValue('L1', '수량')
            ->setCellValue('M1', '주문번호')
            ->setCellValue('N1', '요금납부방법')
            ->setCellValue('O1', '배송메시지')
            ->setCellValue('P1', '비고')
            ->setCellValue('Q1', '반품수거메시지');
    
            $objPHPExcel->getActiveSheet()
            ->getStyle('A1:Q1')
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFd0d0d0');  
        } 
        else {
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '품목명')
            ->setCellValue('B1', '박스수량')
            ->setCellValue('C1', '이름')
            ->setCellValue('D1', '전화번호')
            ->setCellValue('E1', '주소')
            ->setCellValue('F1', '배송메시지')
            ->setCellValue('G1', '운송장번호')
            ->setCellValue('H1', '고객주문번호');

            $objPHPExcel->getActiveSheet()
            ->getStyle('A1:H1')
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFd0d0d0');  
        }

    }
}

//

if ($isReport) {
    $rs = $db->select("as_parcel_service", $_GET['query_where']);
}
else {
//  $rs = $db->select("as_parcel_service", "where process_state=$state order by idx desc");
    if ($state==ST_FIX_DONE) { //발송용 송장 
        $rs = $db->select("as_parcel_service", "where process_state=$state order by update_time asc");
    } 
    else {
        $rs = $db->select("as_parcel_service", "where process_state=$state order by idx desc");
    }
}

//$intN = 1;
$rowIdx = 2;
while( $row = mysqli_fetch_array( $rs ) ) {
//	$tmpN = str_pad( $intN, 5, "0", STR_PAD_LEFT );
	
	if ( ($row['customer_phone'] != "") && 
		 (strlen($row['customer_phone'])==10 || strlen($row['customer_phone'])==11) 
		) {
			$customer_ph1 = substr($row['customer_phone'],0,3);
			$customer_ph3 = substr($row['customer_phone'],-4);
			$customer_ph2 = substr($row['customer_phone'],3,(strlen($row['customer_phone'])-7));
			$customer_phone = $customer_ph1."-".$customer_ph2."-".$customer_ph3;
	}
	if ($customer_phone=="") {
		$customer_phone = $row['customer_phone']; 
	}
    
    if ($isReport) {
        
        $memo = $row['admin_memo'];
        $memo = str_replace("(V)","",$memo);
        $memo = str_replace("(R)","",$memo);
        $memo = str_replace("(H)","",$memo);
        $memo = str_replace("(S)","",$memo);
        $memo = str_replace("(M)","",$memo);
        $memo = str_replace("[ETC]","",$memo);

        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue(sprintf("A%s", $rowIdx), $row['reg_num'])
        ->setCellValue(sprintf("B%s", $rowIdx), $proc_state[$row['process_state']])
        ->setCellValue(sprintf("C%s", $rowIdx), $row['customer_name'])
        ->setCellValue(sprintf("D%s", $rowIdx), $row['customer_phone']) //추가
        ->setCellValue(sprintf("E%s", $rowIdx), $row['product_name'])
        ->setCellValue(sprintf("F%s", $rowIdx), $row['product_date'])
        ->setCellValue(sprintf("G%s", $rowIdx), $row['broken_type'])
        ->setCellValue(sprintf("H%s", $rowIdx), $row['customer_desc'])
        ->setCellValue(sprintf("I%s", $rowIdx), $memo)
        ->setCellValueExplicit(sprintf("J%s", $rowIdx), $row['parcel_num_return'], PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValueExplicit(sprintf("K%s", $rowIdx), $row['price'], PHPExcel_Cell_DataType::TYPE_NUMERIC); //20210105

        $objPHPExcel->setActiveSheetIndex(0)->getStyle(sprintf("K%s", $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_ACCOUNTING); //20210105       
        $objPHPExcel->setActiveSheetIndex(0)->getRowDimension($rowIdx)->setRowHeight(20);
    }
    else {
        if ($state==ST_FIX_DONE) { //발송 송장 
            //20220315
            $memo = $row['admin_memo'];
            $memo = str_replace("(V)","",$memo);
            $memo = str_replace("(R)","",$memo);
            $memo = str_replace("(H)","",$memo);
            $memo = str_replace("(S)","",$memo);
            $memo = str_replace("(M)","",$memo);
            $memo = str_replace("[ETC]","",$memo);
            
            $delivery_msg = $row['parcel_memo_return'];
            $delivery_msg .= '(자사몰AS접수)';
            $delivery_msg .= " ";
            $delivery_msg .= $memo;
            $delivery_msg = $tools->getSubstring($delivery_msg, 100);

            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue(sprintf("A%s", $rowIdx), $row['product_name'])
            ->setCellValue(sprintf("B%s", $rowIdx), 1)
            ->setCellValue(sprintf("C%s", $rowIdx), $row['customer_name'])
            ->setCellValueExplicit(sprintf("D%s", $rowIdx), $customer_phone, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValue(sprintf("E%s", $rowIdx), '')
            ->setCellValue(sprintf("F%s", $rowIdx), $row['customer_addr_return']." ".$row['customer_addr_detail_return'])
            ->setCellValue(sprintf("G%s", $rowIdx), $delivery_msg /*$row['parcel_memo_return'].'(자사몰AS접수)'*/)
            //->setCellValue(sprintf("H%s", $rowIdx), '')
            ->setCellValue(sprintf("H%s", $rowIdx), $row['reg_num']);
        }
        else if ($state==ST_REGISTERING || $state==ST_DC) { //회수 송장
            if (USE_DELIVERY_EPOST) {
                $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue(sprintf("A%s", $rowIdx), $row['customer_name'])
                ->setCellValue(sprintf("B%s", $rowIdx), sprintf("%05d", $row['customer_zipcode']))
                ->setCellValue(sprintf("C%s", $rowIdx), $row['customer_addr'])
                ->setCellValue(sprintf("D%s", $rowIdx), $row['customer_addr_detail'])
                ->setCellValueExplicit(sprintf("E%s", $rowIdx), $customer_phone, PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit(sprintf("F%s", $rowIdx), $customer_phone, PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue(sprintf("G%s", $rowIdx), '전자제품'.'(자사몰AS접수)')
                ->setCellValue(sprintf("H%s", $rowIdx), '')
                ->setCellValue(sprintf("I%s", $rowIdx), '')
                ->setCellValue(sprintf("J%s", $rowIdx), '')
                ->setCellValue(sprintf("K%s", $rowIdx), '')
                ->setCellValue(sprintf("L%s", $rowIdx), '1')
                ->setCellValue(sprintf("M%s", $rowIdx), $row['reg_num'])
                ->setCellValue(sprintf("N%s", $rowIdx), '')
                ->setCellValue(sprintf("O%s", $rowIdx), $row['parcel_memo'])
                ->setCellValue(sprintf("P%s", $rowIdx), '')
                ->setCellValue(sprintf("Q%s", $rowIdx), '');
            }
            else {
                $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue(sprintf("A%s", $rowIdx), $row['product_name'])
                ->setCellValue(sprintf("B%s", $rowIdx), 1)
                ->setCellValue(sprintf("C%s", $rowIdx), $row['customer_name'])
                ->setCellValueExplicit(sprintf("D%s", $rowIdx), $customer_phone, PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue(sprintf("E%s", $rowIdx), $row['customer_addr']." ".$row['customer_addr_detail'])
                ->setCellValue(sprintf("F%s", $rowIdx), $row['parcel_memo'].'(자사몰AS접수)')
                ->setCellValue(sprintf("G%s", $rowIdx), '')
                ->setCellValue(sprintf("H%s", $rowIdx), $row['reg_num']);
            }
        }

    }
    
    
    $rowIdx++;
}

// create file name
if ($state==ST_REGISTERING || $state==ST_DC) {
    $fileName = 'AS택배_수거접수_'.date("Ymd");  
} 
else if ($state==ST_FIX_DONE) {
    $fileName = 'AS택배_발송접수_'.date("Ymd");  
}
else {
    $fileName = 'AS택배_'.date("Ymd");
}

if ($isReport) {
    $fileName = 'AS조회검색_'.date("Ymd");
}


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');


/*
// redirect output to a client's web browser
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="'.$fileName.'.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');
    
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
*/

$objWriter->save('php://output');

// disconnect
$objPHPExcel->disconnectWorksheets();
$objPHPExcel->garbageCollect();

unset( $objWriter, $objPHPExcel );

$temp_fileName = $fileName.'.xlsx';
$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='excel_dl_as', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$temp_fileName' ");

exit;

?>