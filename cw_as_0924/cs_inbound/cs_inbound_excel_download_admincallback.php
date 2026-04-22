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

$callback = isset($_GET['callback']) ? $_GET['callback'] : 0;

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
        'size'  => 11,
        'name'  => '맑은 고딕'
    ));

$objPHPExcel->getDefaultStyle()->applyFromArray($styleArray);    
$sheet->getRowDimension(1)->setRowHeight(35);
$sheet->getRowDimension(2)->setRowHeight(17);
$sheet->getDefaultRowDimension()->setRowHeight(17);

$sheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getDefaultStyle()->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);


$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(25);
$sheet->getColumnDimension('F')->setWidth(30);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(30);
$sheet->getColumnDimension('I')->setWidth(0);

$sheet->setAutoFilter('A2:I2');

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

$sheet->mergeCells('A1:I1')->setCellValue('A1', sprintf("CS 관리자 전화 요청 리스트 (%s ~ %s)", $date_from, $date_to) );
$sheet->setCellValue('A2', "등록일");
$sheet->setCellValue('B2', "요청자");
$sheet->setCellValue('C2', "고객명");
$sheet->setCellValue('D2', "전화번호");
$sheet->setCellValue('E2', "제품명");
$sheet->setCellValue('F2', "요청사항");
$sheet->setCellValue('G2', "처리자");
$sheet->setCellValue('H2', "처리내역");
$sheet->setCellValue('I2', "처리결과");

$sheet->getStyle("A")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("B")->getAlignment()->setWrapText(true); 
$sheet->getStyle("D")->getAlignment()->setWrapText(true); 
$sheet->getStyle("F")->getAlignment()->setWrapText(true); 

$sheet->getStyle("F")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet->getStyle("H")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$sheet->getStyle(sprintf("A1:I1"))->applyFromArray(array(
    'borders'  => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        ),
    ),
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => '000000'),
        'size'  => 12,
        'name'  => '맑은 고딕'
    )
));

$sheet->getStyle(sprintf("A2:I2"))->applyFromArray(array(
    'borders'  => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        ),
    ),
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => 'ffffff'),
        'size'  => 11,
        'name'  => '맑은 고딕'
    ),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => '76933c')
    )
));

$query = "select * FROM $table WHERE reg_datetime BETWEEN '$date_from' AND '$date_to2' ";

if ($callback){
    $query = "select * FROM $table WHERE (CHAR_LENGTH(pic_memo)>0) and reg_datetime BETWEEN '$date_from' AND '$date_to2' ";
}

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
    $sheet->setCellValue(sprintf("A%d", $rowIdx), date("Y-m-d", strtotime($row['reg_datetime'])) );
    $sheet->setCellValue(sprintf("B%d", $rowIdx), $row['pic_name'] );
    $sheet->setCellValue(sprintf("C%d", $rowIdx), $row['customer_name'] );
    $sheet->setCellValue(sprintf("D%d", $rowIdx), $row['customer_phone'] );
    $sheet->setCellValue(sprintf("E%d", $rowIdx), $row['product_name'] );
    $sheet->setCellValue(sprintf("F%d", $rowIdx), $row['pic_memo'] );

    $sheet->setCellValue(sprintf("G%d", $rowIdx), $row['admin_name'] );
    $sheet->setCellValue(sprintf("H%d", $rowIdx), $row['admin_desc'] );
    $sheet->setCellValue(sprintf("I%d", $rowIdx), $arr_inbound_result_type[$row['admin_result']] );
    
//    $sheet->getRowDimension($rowIdx)->setRowHeight(17);

    $sheet->getStyle(sprintf("F%s:H%s", $rowIdx, $rowIdx))->getAlignment()->setWrapText(true);

    if ($row['admin_result']==1) {
        $sheet->getStyle(sprintf("A%s:I%s", $rowIdx, $rowIdx))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aaaaaa");
    }

    $sheet->getStyle(sprintf("A%d:I%d", $rowIdx, $rowIdx))->applyFromArray(array(
        'borders'  => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            ),
        )
    ));
    
    $rowIdx++;
}

$sheet->getStyle(sprintf("A3:I%d", $rowIdx-1))->applyFromArray(array(
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
$fileName = "CS관리자전화요청리스트";
$temp_fileName = $fileName.'.xlsx';

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

$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='excel_dl_inbound', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$temp_fileName' ");

exit;

?>