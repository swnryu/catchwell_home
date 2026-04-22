<?
session_start();

include ("../common.php");
require ("../check_session.php");
include("event_def.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';


//품목명	박스수량	받는분성명	받는분전화번호	받는분주소	배송메세지1	쇼핑몰	주문번호	아이디	구입일	제품명	메모


$from = isset($_GET['from']) ? $_GET['from'] : "";
$to = isset($_GET['to']) ? $_GET['to'] : "";
$to2 = date("Y-m-d", strtotime($to." +1 day"));
$view_mode = 0;//isset($_GET['view_mode']) ? $_GET['view_mode'] : 0;


// create new PHPExcel object
$objPHPExcel = new PHPExcel();

//$objPHPExcel->getActiveSheet()->getFont()->setName("Arial")->setSize(8);
$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(16);
$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(8);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
//$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
$objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setWrapText(true);
//$objPHPExcel->getActiveSheet()->getStyle('L')->getAlignment()->setWrapText(true);
//$objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setWrapText(true);


$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '품목명')
        ->setCellValue('B1', '홈페이지ID')
        ->setCellValue('C1', '받는분성명')
        ->setCellValue('D1', '받는분전화번호')
        ->setCellValue('E1', '받는분주소')
        ->setCellValue('F1', '시리얼번호')
        ->setCellValue('G1', '쇼핑몰')
        ->setCellValue('H1', '주문번호')
        ->setCellValue('I1', '아이디')    
        ->setCellValue('J1', '구입일')    
        ->setCellValue('K1', '제품명')
        ->setCellValue('L1', '상태');

$objPHPExcel->getActiveSheet()
        ->getStyle('A1:L1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('FFeeeeee');

$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getBorders()->getAllBorders()->getColor()->setARGB("FFD0D0D0");
$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray(array(
        'borders' => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        )));  


$objPHPExcel->setActiveSheetIndex(0)->getRowDimension(1)->setRowHeight(23);
$objPHPExcel->getActiveSheet()->freezePane('A2');


$where = "where date between date('$from') and date('$to2')";

if ($view_mode == 1)      {$where.=" and (status is null OR status=0)";}
else if ($view_mode == 2) {$where.=" and status=1";}


$where .= " order by idx desc";
$rs = $db->select("lab_online_event", $where);


$rowIdx = 2;
while( $row = mysqli_fetch_array( $rs ) ) {
 
    if($row['status']=='' || $row['status']==0)        { $status="처리중"; }
    else if($row['status']==1)                         { $status="완료"; } 

        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue(sprintf("A%s", $rowIdx), $row['gift']) //품목명
        ->setCellValue(sprintf("B%s", $rowIdx), $row['homepage_id']) //홈페이지ID
        ->setCellValue(sprintf("C%s", $rowIdx), $row['customer_name']) //이름
        ->setCellValue(sprintf("D%s", $rowIdx), $row['customer_phone']) //연락처
        ->setCellValue(sprintf("E%s", $rowIdx), $row['customer_addr'].' '.$row['customer_addr_detail']) //주소 
        ->setCellValue(sprintf("F%s", $rowIdx), strtoupper($row['serial_no'])) //시리얼번호
        ->setCellValue(sprintf("G%s", $rowIdx), $row['market_name']) //쇼핑몰
        ->setCellValueExplicit(sprintf("H%s", $rowIdx), sprintf("%s", $row['order_id']), PHPExcel_Cell_DataType::TYPE_STRING) //주문번호
        ->setCellValue(sprintf("I%s", $rowIdx), $row['market_id']) //아이디
        ->setCellValue(sprintf("J%s", $rowIdx), $row['order_date']) //구입일
        ->setCellValue(sprintf("K%s", $rowIdx), $row['model_name'].$row['gift']) //제품명
        ->setCellValue(sprintf("L%s", $rowIdx), ($row['status']==1) ? "완료" : "처리중" ) ; //상태 

        $objPHPExcel->setActiveSheetIndex(0)->getRowDimension($rowIdx)->setRowHeight(30);

    $rowIdx++;
}






// create file name
$fileName = '이벤트응모_'.date("Ymd").'.xlsx';


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename='.$fileName);
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objWriter->save('php://output');

// disconnect
$objPHPExcel->disconnectWorksheets();
$objPHPExcel->garbageCollect();


$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='excel_dl_ev', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$fileName' ");

unset( $objWriter, $objPHPExcel );

exit;

?>