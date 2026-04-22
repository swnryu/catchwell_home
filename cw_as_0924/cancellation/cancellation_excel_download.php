<?//session_name("CW_AS");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', false);
ini_set('display_startup_errors', false);

include ("../common.php");
require ("../check_session.php");
include ("../def_inc.php");
include ("cancellation_def.php");


require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';


$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : "";
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : "";

$year = date_format(date_create($date_to), "Y");
$month = date_format(date_create($date_to), "m");
$month_from = sprintf("%d-%02d-01", $year, $month);
$month_to = $date_to;

$date_to2 = date("Y-m-d", strtotime($date_to." +0 day"));
$month_to2 = date("Y-m-d", strtotime($month_to." +0 day"));

//file name
$from = preg_replace("/[^0-9]*/s", "", $date_from);
$to = preg_replace("/[^0-9]*/s", "", $date_to);
$target_file = "CS반품리스트_".$from."_".$to;
$template_file = "template/cs_cancellation_list_template.xlsx";


//query
$rs = $db->select("cancellation_order", $_GET['query_where']);



$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load($template_file);
$sheet = $objPHPExcel->getActiveSheet();
//$sheet->getColumnDimension('B')->setWidth(9);
$sheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


$rowIdx = 3;
while( $row = mysqli_fetch_array( $rs ) ) {

    $result = "";
    if ($row['result_type'] == 0) {
        $result = $result_type[0] . " " . $row['result_memo'];
    }
    else if ($row['result_type'] == 1) {
        $result = $result_type[1] . " " . $row['result_memo'];
    }
    else if ($row['result_type'] == 2) {
        $result = $result_type[2] . " " . $row['result_memo'];
    }

    if ($row['exchange_order'] != "") {
        $result = $result . "새상품 재출고(". $row['exchange_order'] . ")";
    }

    //
    $date_completed = "";
    if ($row['status'] == 1) {
        $date_completed = date('m/d', strtotime($row['date_completed'])) . " " . $row['type'] ." 완료";
    }
    
    $objPHPExcel->getActiveSheet()->getStyle(sprintf("I%s:M%s", $rowIdx, $rowIdx))->getAlignment()->setWrapText(true);

    //
    include ("replacement_tracking_from_salesdb.php");
    
    $memo = $row['memo'];
    if($tracking_sales!="") {
        $memo = ($tracking_sales . "\n") . $memo;
    }

    //
    $objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue(sprintf("B%s", $rowIdx), $row['admin_name'])
    ->setCellValue(sprintf("C%s", $rowIdx), $row['date'])
    ->setCellValue(sprintf("D%s", $rowIdx), $row['model_name'])
    ->setCellValueExplicit(sprintf("E%s", $rowIdx), $row['order_id'], PHPExcel_Cell_DataType::TYPE_STRING) 
    ->setCellValue(sprintf("F%s", $rowIdx), $row['shopping_mall'])
    ->setCellValue(sprintf("G%s", $rowIdx), $row['type'])
    ->setCellValue(sprintf("H%s", $rowIdx), $row['customer_name'])
    ->setCellValue(sprintf("I%s", $rowIdx), $row['reason'])
    ->setCellValue(sprintf("J%s", $rowIdx), $date_completed /*$row['date_completed']*/) //완료일 / 처리결과
    ->setCellValueExplicit(sprintf("K%s", $rowIdx), $memo, PHPExcel_Cell_DataType::TYPE_STRING) //송장번호,고객조율사항
    //->setCellValue(sprintf("K%s", $rowIdx), $memo /*$row['memo']*/) //고객조율사항
    ->setCellValue(sprintf("L%s", $rowIdx), $row['serial']) //시리얼번호
    ->setCellValue(sprintf("M%s", $rowIdx), $result /*$row['result_memo']*/); //처리내역
 
    //$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($rowIdx)->setRowHeight(20);

    if ($row['status']==1) {
        $objPHPExcel->getActiveSheet()->getStyle(sprintf("B%s:M%s", $rowIdx, $rowIdx))->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("DDDDDDDD");
    }

    $rowIdx++;
}




//wirte file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="'.$target_file.'.xlsx"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

unset( $objWriter, $objPHPExcel );

$temp_fileName = $target_file.'xlsx';
$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='excel_dl_cancel', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$temp_fileName' ");

?>