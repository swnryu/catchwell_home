<?
session_start();

error_reporting(E_ALL);
ini_set('display_errors', false);
ini_set('display_startup_errors', false);

include ("../common.php");
require ("../check_session.php");
include ("../def_inc.php");
include ("cs_inbound_def.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';


//param: date_from=2021-11-02&date_to=2021-11-12&search_item=&search_order=CV7&inquiry_type=1

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : "";
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : "";
$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));

$search_item = isset($_GET['search_item']) ? $_GET['search_item'] : "";
$search_order = isset($_GET['search_order']) ? $_GET['search_order'] : "";
$inquiry_type = isset($_GET['inquiry_type']) ? $_GET['inquiry_type'] : 0;

//echo $date_from."<br>";
//echo $date_to."<br>";
//echo $date_to2."<br>";

$table = "cs_inbound_call";


$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet();

$styleArray = array(
    'font'  => array(
        'bold'  => false,
        'color' => array('rgb' => '000000'),
        'size'  => 10,
        'name'  => '맑은 고딕'
    ));

$objPHPExcel->getDefaultStyle()->applyFromArray($styleArray);    
$sheet->getRowDimension(1)->setRowHeight(30);
$sheet->getRowDimension(2)->setRowHeight(17);
$sheet->getDefaultRowDimension()->setRowHeight(17);

$sheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getDefaultStyle()->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);

//등록일시/모델명/문의유형/불량고객(강성)/담당자/관리자메모

$sheet->getColumnDimension('A')->setWidth(23);
$sheet->getColumnDimension('B')->setWidth(28);
$sheet->getColumnDimension('C')->setWidth(24);
$sheet->getColumnDimension('D')->setWidth(30);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(30);

//
$search = " - ";
if ($search_order!="")
{
    $search .= $search_order." / ";
}
if ($search_item=="black_consumer") 
{
    $search .= "불량고객"." / ";
}
$search .= $arr_inbound_call_type[$inquiry_type];

$sheet->mergeCells('A1:F1')->setCellValue('A1', sprintf("CS 콜리스트 조회 결과 (%s ~ %s) %s ", $date_from, $date_to, $search) );
$sheet->setCellValue('A2', "등록일시");
$sheet->setCellValue('B2', "모델명");
$sheet->setCellValue('C2', "문의유형");
$sheet->setCellValue('D2', "불량고객(강성)");
$sheet->setCellValue('E2', "담당자");
$sheet->setCellValue('F2', "관리자메모");

$sheet->getStyle("A")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("B")->getAlignment()->setWrapText(true); 
$sheet->getStyle("D")->getAlignment()->setWrapText(true); 
$sheet->getStyle("F")->getAlignment()->setWrapText(true); 

$sheet->getStyle(sprintf("A1:F2"))->applyFromArray(array(
    'borders'  => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        ),
    ),
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => '000000'),
        'size'  => 10,
        'name'  => '맑은 고딕'
    ),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'c4bd97')
    )
));


$query = "select * FROM $table WHERE reg_datetime BETWEEN '$date_from' AND '$date_to2' ";

if($search_order){
    if($search_item){
        $query.="and $search_item like '%$search_order%' ";
    }else{
        $query.="and (product_name like '%$search_order%' or black_consumer_desc like '%$search_order%' or pic_name like '%$search_order%' ) ";
    }
}
else if ($search_item=="black_consumer") 
{
    $query.="and $search_item = 1 ";
}
if ($inquiry_type > 0) 
{
    $query.="and (inquiry_type=$inquiry_type) ";
    $query_where.="and (inquiry_type=$inquiry_type) ";
}
$query.=" order by idx desc";

//echo $query."<br>";
$rs	= mysqli_query($db->db_conn, $query);

$rowIdx = 3;
while ($row = mysqli_fetch_array( $rs ))
{
    $sheet->setCellValue(sprintf("A%d", $rowIdx), $row['reg_datetime'] );
    $sheet->setCellValue(sprintf("B%d", $rowIdx), $row['product_name'] );
    $sheet->setCellValue(sprintf("C%d", $rowIdx), $arr_inbound_call_type[ $row['inquiry_type'] ] );
    if($row['black_consumer'])
    {
        $sheet->setCellValue(sprintf("D%d", $rowIdx), "[강성]".$row['black_consumer_desc'] );
    }
    $sheet->setCellValue(sprintf("E%d", $rowIdx), $row['pic_name'] );
    $sheet->setCellValue(sprintf("F%d", $rowIdx), $row['pic_memo'] );

//    $sheet->getRowDimension($rowIdx)->setRowHeight(17);

//    $sheet->getStyle(sprintf("B%s:F%s", $rowIdx, $rowIdx))->getAlignment()->setWrapText(true);

    $rowIdx++;
}

$sheet->getStyle(sprintf("A3:F%d", $rowIdx-1))->applyFromArray(array(
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
    )
));


//틀고정
$sheet->freezePane('A3');

// create file name 
$fileName = "CS콜리스트_조회결과";


$fileName= mb_convert_encoding($fileName, 'euc-kr', 'UTF-8');


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-type: charset=utf-8");
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

exit;

?>