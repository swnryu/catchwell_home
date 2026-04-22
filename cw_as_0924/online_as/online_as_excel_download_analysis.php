<?//session_name("CW_AS");
session_start();

include ("../common.php");
require ("../check_session.php");
include ("../def_inc.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';

const FORMAT_NUMBER_COMMA_SEPARATED3 = '#,##0';
const FORMAT_CODE_ACCOUNTING = '_-* #,##0_-;-* #,##0_-;_-* "-"_-;_-@_-';


//$state = isset($_GET['state']) ? $_GET['state'] : 0;

//$isReport = isset($_GET['query']);


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

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);

$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('A1', 'NO')
->setCellValue('B1', '최종업데이트') 
->setCellValue('C1', '접수번호') 
->setCellValue('D1', '상태') 
->setCellValue('E1', '이름')
->setCellValue('F1', '전화번호') //필드 추가
->setCellValue('G1', '모델명')
->setCellValue('H1', '구매일')
->setCellValue('I1', '불량유형')
->setCellValue('J1', '불량내용')
->setCellValue('K1', '조치사항');

$objPHPExcel->getActiveSheet()
->getStyle('A1:K1')
->getFill()
->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
->getStartColor()
->setARGB('FFd0d0d0');
    


//

$query = $_GET['query'];//"select *, COUNT(*) as cnt from as_parcel_service GROUP BY customer_phone, product_name HAVING COUNT(*) > 1 ORDER BY cnt desc";
$result = mysqli_query($db->db_conn, $query);
$listNo = mysqli_num_rows($result);

$rowIdx = 2;
while( $row = mysqli_fetch_array( $result ) ) {

    //20210220-01000000000 제외
    $query = "select * from as_parcel_service where process_state>3 and customer_phone='$row[customer_phone]' and customer_phone!='01000000000' ORDER BY update_time desc limit $row[cnt] ";
    $rs = mysqli_query($db->db_conn, $query);
    $i = 0;
    while($rs_row = mysqli_fetch_array($rs)){
/*    
        if ($i==0) {
            $objPHPExcel->getActiveSheet()
            ->getStyle(sprintf("A%s:K%s", $rowIdx, $rowIdx)) 
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('eee0e0e0');
        }
*/
        $i++;
	
        if ( ($rs_row['customer_phone'] != "") && 
            (strlen($rs_row['customer_phone'])==10 || strlen($rs_row['customer_phone'])==11) 
            ) {
                $customer_ph1 = substr($rs_row['customer_phone'],0,3);
                $customer_ph3 = substr($rs_row['customer_phone'],-4);
                $customer_ph2 = substr($rs_row['customer_phone'],3,(strlen($rs_row['customer_phone'])-7));
                //$customer_phone = $customer_ph1."-".$customer_ph2."-".$customer_ph3;
                $customer_phone = $customer_ph1."-"."****"."-".$customer_ph3;
        }
        if ($customer_phone=="") {
            $customer_phone = $rs_row['customer_phone']; 
        }
    

        
        $memo = $rs_row['admin_memo'];
        $memo = str_replace("(V)","",$memo);
        $memo = str_replace("(R)","",$memo);
        $memo = str_replace("(H)","",$memo);
        $memo = str_replace("(S)","",$memo);
        $memo = str_replace("(M)","",$memo);
        $memo = str_replace("[ETC]","",$memo);

        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue(sprintf("A%s", $rowIdx), $listNo)
        ->setCellValue(sprintf("B%s", $rowIdx), date_format(date_create($rs_row['update_time']),"Y-m-d"))
        ->setCellValue(sprintf("C%s", $rowIdx), $rs_row['reg_num'])//$proc_state[$rs_row['process_state']])
        ->setCellValue(sprintf("D%s", $rowIdx), $proc_state[$rs_row['process_state']])
        ->setCellValue(sprintf("E%s", $rowIdx), $rs_row['customer_name']." (".$row['cnt'].")")
        ->setCellValue(sprintf("F%s", $rowIdx), $customer_phone) 
        ->setCellValue(sprintf("G%s", $rowIdx), $rs_row['product_name'])
        ->setCellValue(sprintf("H%s", $rowIdx), $rs_row['product_date'])
        ->setCellValue(sprintf("I%s", $rowIdx), $rs_row['broken_type'])
        ->setCellValue(sprintf("J%s", $rowIdx), $rs_row['customer_desc'])
        ->setCellValue(sprintf("K%s", $rowIdx), $memo);
//        ->setCellValueExplicit(sprintf("J%s", $rowIdx), $rs_row['parcel_num_return'], PHPExcel_Cell_DataType::TYPE_STRING)
//        ->setCellValueExplicit(sprintf("K%s", $rowIdx), $rs_row['price'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

//        $objPHPExcel->setActiveSheetIndex(0)->getStyle(sprintf("K%s", $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_ACCOUNTING);

        $objPHPExcel->setActiveSheetIndex(0)->getRowDimension($rowIdx)->setRowHeight(30);
     
       $rowIdx++;
    }

    $listNo--;
}

$fileName = 'AS통계_2회이상접수_'.date("Ymd");


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

exit;

?>