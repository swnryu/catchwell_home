<?//session_name("CW_AS");
session_start();

include ("../common.php");
require ("../check_session.php");
include ("../def_inc.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';



// create new PHPExcel object
$objPHPExcel = new PHPExcel();

$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);



$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);


//$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
 
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'NO')
    ->setCellValue('B1', '모델명') 
    ->setCellValue('C1', '구매처')
    ->setCellValue('D1', '이름')
    ->setCellValue('E1', '시리얼번호')
    ->setCellValue('F1', '출고일')
    ->setCellValue('G1', '비고');

$objPHPExcel->getActiveSheet()
    ->getStyle('A1:G1')
    ->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('FFd0d0d0');

//
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : "";
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : ""; 
$date_to2 = date("Y-m-d", strtotime($date_to." +0 day"));
$search_item = isset($_GET['search_item']) ? $_GET['search_item'] : "";
$search_order = isset($_GET['search_order']) ? $_GET['search_order'] : "";

$where = "where status=1 and date between date('$date_from') and date('$date_to2') ";
if($search_order){
    if($search_item){
        $where.="and $search_item like '%$search_order%' ";
    }else{
        $where.="and model like '%$search_order%' or serial like '%$search_order%' ";
    }
}
$where .= " order by idx asc ";
$rs = $db->select("shipping_date_new", $where);

$rs_cnt = mysqli_num_rows($rs);

$rowIdx = 2;
while( $row = mysqli_fetch_array( $rs ) ) {
 
    $objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue(sprintf("A%s", $rowIdx), $rs_cnt)
    ->setCellValue(sprintf("B%s", $rowIdx), $row['model'])
    ->setCellValue(sprintf("C%s", $rowIdx), $row['mall'])
    ->setCellValue(sprintf("D%s", $rowIdx), $row['name'])
    ->setCellValue(sprintf("E%s", $rowIdx), $row['serial'])
    ->setCellValue(sprintf("F%s", $rowIdx), $row['date'])
    ->setCellValue(sprintf("G%s", $rowIdx), $row['memo']);

//    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E2", $where);
    $objPHPExcel->setActiveSheetIndex(0)->getRowDimension($rowIdx)->setRowHeight(20);
    
    $rowIdx++;
    $rs_cnt--;
}

// create file name
$fileName = '출고조회_'.date("Ymd");
$temp_fileName = $fileName.'xlsx';

$fileName= mb_convert_encoding($fileName, 'euc-kr', 'UTF-8');

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


$objWriter->save('php://output');

// disconnect
$objPHPExcel->disconnectWorksheets();
$objPHPExcel->garbageCollect();

unset( $objWriter, $objPHPExcel );

$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='excel_dl_ship', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$temp_fileName' ");

exit;

?>