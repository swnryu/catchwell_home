<?
session_start();

include ("../common.php");
require ("../check_session.php");
include ("../def_inc.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';

const FORMAT_CODE_ACCOUNTING = '_-* #,##0_-;-* #,##0_-;_-* "-"_-;_-@_-';

// create new PHPExcel object
$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet();

$styleArray = array(
    'font'  => array(
        'bold'  => false,
        'color' => array('rgb' => '000000'),
        'size'  => 9,
        'name'  => '맑은 고딕'
    ));

$sheet->getDefaultStyle()->applyFromArray($styleArray);
$sheet->getDefaultColumnDimension()->setWidth(15);
$sheet->getDefaultRowDimension()->setRowHeight(17);
$sheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getDefaultStyle()->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);

$sheet->getDefaultStyle()->getFont()->setSize(10);
$sheet->getColumnDimension('A')->setWidth(12);
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(10);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(28);
$sheet->getColumnDimension('H')->setWidth(20);
$sheet->getColumnDimension('I')->setWidth(24);
$sheet->getColumnDimension('J')->setWidth(12);
$sheet->getColumnDimension('K')->setWidth(18);
$sheet->getColumnDimension('L')->setWidth(10);
$sheet->getColumnDimension('M')->setWidth(16);



$sheet->mergeCells('A1:M1')->setCellValue('A1', "사내판매 출고 리스트");
$sheet->getStyle(sprintf("A1:M1"))->applyFromArray(array(
    'borders'  => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        ),
    ),
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => '000000'),
        'size'  => 25,
        'name'  => '맑은 고딕'
    )
));

$sheet->getRowDimension(1)->setRowHeight(60);
$sheet->getRowDimension(2)->setRowHeight(25);

$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A2', '날짜')
    ->setCellValue('B2', '제품명') 
    ->setCellValue('C2', '기타')
    ->setCellValue('D2', '가격')
    ->setCellValue('E2', '고객명')
    ->setCellValue('F2', '연락처')
    ->setCellValue('G2', '주소')
    ->setCellValue('H2', '배송메시지')
    ->setCellValue('I2', '판매경로')
    ->setCellValue('J2', '요청자')
    ->setCellValue('K2', '담당자메모')
    ->setCellValue('L2', '처리상태')
    ->setCellValue('M2', '송장번호');

$sheet->getStyle(sprintf("A2:M2"))->applyFromArray(array(
    'borders'  => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        ),
    ),
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => '000000'),
        'size'  => 11,
        'name'  => '맑은 고딕'
    )
));

$sheet->getStyle('A2:M2')
    ->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('FFa9d08e');

$sheet->setAutoFilter('A2:M2');
$sheet->freezePane('A3');
$sheet->getStyle("B")->getAlignment()->setWrapText(true); 
$sheet->getStyle("C")->getAlignment()->setWrapText(true); 

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : "";
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : ""; 
$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));
$search_item = isset($_GET['search_item']) ? $_GET['search_item'] : "";
$search_order = isset($_GET['search_order']) ? $_GET['search_order'] : "";

$where = "where status=1 and reg_datetime between date('$date_from') and date('$date_to2') ";
if($search_order){
    if($search_item){
        $where.="and $search_item like '%$search_order%' ";
    }else{
        $where.="and model like '%$search_order%' or serial like '%$search_order%' ";
    }
}
$where .= " order by idx desc ";
$rs = $db->select("cs_internal_orders", $where);

$rs_cnt = mysqli_num_rows($rs);

$rowIdx = 3;
while( $row = mysqli_fetch_array( $rs ) ) {
 
    if($row['status']==0) {
        $status="처리중";
    } else {
        $status="처리완료";
    }

    $parts_name = rtrim($row['parts_name'],";");
    $parts_name = str_replace(";",", ",$parts_name);
    $parts_name_ex = rtrim($row['parts_name_ex'],";");
    if ($parts_name_ex != "")
    {
        $parts = str_replace("(V)","",$parts_name).", ".$parts_name_ex; //20211213
    }
    else
    {
        $parts = str_replace("(V)","",$parts_name).$parts_name_ex; //20211213
    }
    $parts = ltrim($parts,", ");

    
    $sheet
    ->setCellValue(sprintf("A%s", $rowIdx), date("Y-m-d", strtotime($row['reg_datetime'])))
    ->setCellValue(sprintf("B%s", $rowIdx), $row['product_name'])
    ->setCellValue(sprintf("C%s", $rowIdx), $parts )

    ->setCellValueExplicit(sprintf("D%s", $rowIdx), $row['parts_price'], PHPExcel_Cell_DataType::TYPE_NUMERIC)

    ->setCellValue(sprintf("E%s", $rowIdx), $row['customer_name'])
    ->setCellValue(sprintf("F%s", $rowIdx), $row['customer_phone'])
    ->setCellValue(sprintf("G%s", $rowIdx), $row['customer_addr'])
    ->setCellValue(sprintf("H%s", $rowIdx), $row['delivery_memo'])
    ->setCellValue(sprintf("I%s", $rowIdx), $row['reason'])
    ->setCellValue(sprintf("J%s", $rowIdx), $row['pic_name'])
    ->setCellValue(sprintf("K%s", $rowIdx), $row['pic_memo'])
    ->setCellValue(sprintf("L%s", $rowIdx), $status )
    ->setCellValueExplicit(sprintf("M%s", $rowIdx), $row['delivery_num'], PHPExcel_Cell_DataType::TYPE_STRING);

    $sheet->getStyle(sprintf("D%s", $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_ACCOUNTING);
    $sheet->getStyle(sprintf("G%s:K%s", $rowIdx, $rowIdx))->getAlignment()->setWrapText(true);
    
    $rowIdx++;
    $rs_cnt--;
}

$sheet->getStyle(sprintf("A3:M%s", $rowIdx-1))->applyFromArray(array(
    'borders'  => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        ),
    ),
    'font'  => array(
        'bold'  => false,
        'color' => array('rgb' => '000000'),
        'size'  => 9,
        'name'  => '맑은 고딕'
    )
));

// create file name
$fileName = '사내판매출고리스트_'.date("Ymd");
$temp_fileName = $fileName.'.xlsx';

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

$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='excel_dl_internal_orders', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$temp_fileName' ");

exit;

?>