<?
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


const FORMAT_NUMBER_COMMA_SEPARATED3 = '#,##0';
const FORMAT_CODE_ACCOUNTING = '_-* #,##0_-;-* #,##0_-;_-* "-"_-;_-@_-';
const FORMAT_CODE_PERCENTAGE = '0.0%'; ;//'#,##0.0';


$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : "";


$year = date_format(date_create($date_from), "Y");
$month = date_format(date_create($date_from), "m");


include("../product_category_inc.php");

$arr_model_name = array();
for($i=0;$i<count($arr_category_name);$i++)
{
    for($j=0;$j<count($arr_cancellation_model[$i]);$j++)
    {
        array_push($arr_model_name, $arr_cancellation_model[$i][$j]);
    }
}

//$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));
//$month_to2 = date("Y-m-d", strtotime($month_to." +1 day"));


$objPHPExcel = new PHPExcel();

$styleArray = array(
    'font'  => array(
        'bold'  => false,
        'color' => array('rgb' => '000000'),
        'size'  => 10,
        'name'  => '맑은고딕'
    ));

$objPHPExcel->getDefaultStyle()->applyFromArray($styleArray);    

//A1:J1 2월 교환 반품 현황
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:J1')->setCellValue('A1', $year."년 ".$month."월 교환 반품 현황");
$objPHPExcel->setActiveSheetIndex(0)->getRowDimension(1)->setRowHeight(30);
$objPHPExcel->setActiveSheetIndex(0)->getRowDimension(2)->setRowHeight(15);
$objPHPExcel->setActiveSheetIndex(0)->getRowDimension(3)->setRowHeight(15);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);

//A모델명  B월 출고수량  C교환수량  D교환률  E반품수량  F반품률  G:J반품유형
//						                                     G변심반품  H반품률 I불량반품  J반품률
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:A3')->setCellValue('A2', "모델명");
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B2:B3')->setCellValue('B2', "월 출고수량");
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('C2:C3')->setCellValue('C2', "교환수량");
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('D2:D3')->setCellValue('D2', "교환률(%)");
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E2:E3')->setCellValue('E2', "반품수량");
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('F2:F3')->setCellValue('F2', "반품률(%)");
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('G2:J2')->setCellValue('G2', "반품유형");

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G3', "변심반품");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H3', "반품률(%)");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I3', "불량반품");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J3', "반품률(%)");

$objPHPExcel->setActiveSheetIndex(0)->getStyle('A2:F2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A2:F2')->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('G2:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('G2:J3')->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);

$objPHPExcel->getActiveSheet()->getStyle('A2:B2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aaC4D79B");
$objPHPExcel->getActiveSheet()->getStyle('C2:D2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aaccc0da");
$objPHPExcel->getActiveSheet()->getStyle('E2:F2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aa8db4e2");
$objPHPExcel->getActiveSheet()->getStyle('G2:J3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aab8cce4");


$rowIdx_start = $rowIdx = 4;

for ($i=0; $i < count($arr_model_name); $i++) 
{

//query
//$query = "select month(date) as month, model_name, COUNT(*) as cnt FROM cancellation_order WHERE year(date)=$year and month(date)=$month and status=1 GROUP BY model_name";
$query = "select month(date) as month, model_name, COUNT(*) as cnt FROM cancellation_order WHERE year(date)=$year and month(date)=$month and status=1 and model_name='$arr_model_name[$i]' ";
$rs	= mysqli_query($db->db_conn, $query);

//$rowIdx_start = $rowIdx = 4;

//while( $row = mysqli_fetch_array( $rs ) ) {

    $row = mysqli_fetch_array( $rs );

    $name1 = $arr_model_name[$i];
    $cnt1 = $row['cnt'];

//    $name1 = $row['model_name'];
//    $cnt1 = $row['cnt'];

    $shipment_cnt = 0;
    $cancellation_cnt = 0;
    $model_ex1 = $name1 . "_새상품 재출고";
    $model_ex2 = $name1 . "_리퍼";
    $model_ex3 = $name1 . "_사은품";
    $model_ex4 = $name1 . "_리퍼_재출고";
    $model_ex5 = $name1 . "_기존버전_새상품 재출고";

    $query = "select model, COUNT(*) as cnt FROM shipping_date_new WHERE year(date)=$year and month(date)=$month and (model='$name1' or model='$model_ex1' or model='$model_ex2' or model='$model_ex3' or model='$model_ex4' or model='$model_ex5') ";
    $rs2	= mysqli_query($db->db_conn, $query);
    $row3 = mysqli_fetch_array( $rs2 );
    if ($row3['cnt']=="") {
        $shipment_cnt = 0;
    } else {
        $shipment_cnt = $row3['cnt'];
    }

    $query = "select model_name, type, COUNT(*) as cnt FROM cancellation_order WHERE year(date)=$year and month(date)=$month and model_name='$name1' and status=1 GROUP BY type";
    $rs2	= mysqli_query($db->db_conn, $query);

    $row2 = mysqli_fetch_array( $rs2 );
    if ($shipment_cnt==0 && $row2['cnt']==0) {
        continue;
    }

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("C%s", $rowIdx), 0);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("G%s", $rowIdx), 0);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("I%s", $rowIdx), 0);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("A%s", $rowIdx), $name1 ); //모델명
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("B%s", $rowIdx), $shipment_cnt); //출고수량

    mysqli_data_seek($rs2, 0);
    while( $row2 = mysqli_fetch_array( $rs2 ) ) {

        if ($row2['type'] == $cancellation_type[0]) { //교환
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("C%s", $rowIdx), $row2['cnt']);
        }
        else if ($row2['type'] == $cancellation_type[1] ) {//변심
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("G%s", $rowIdx), $row2['cnt']);
            $cancellation_cnt += $row2['cnt'];
        }
        else if ($row2['type'] == $cancellation_type[2]) {//불량
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("I%s", $rowIdx), $row2['cnt']);
            $cancellation_cnt += $row2['cnt'];
        }
    }

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("E%s", $rowIdx), $cancellation_cnt);

    //percentage
    if ($shipment_cnt > 0) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("D%s", $rowIdx), sprintf("=C%s/B%s", $rowIdx, $rowIdx));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("F%s", $rowIdx), sprintf("=E%s/B%s", $rowIdx, $rowIdx));
    }
    else {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("D%s", $rowIdx), 0);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("F%s", $rowIdx), 0);
    }

    if ($cancellation_cnt > 0) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("H%s", $rowIdx), sprintf("=G%s/E%s", $rowIdx, $rowIdx));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("J%s", $rowIdx), sprintf("=I%s/E%s", $rowIdx, $rowIdx));
    } 
    else {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("H%s", $rowIdx), 0);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("J%s", $rowIdx), 0);
    }
    
    $objPHPExcel->setActiveSheetIndex(0)->getRowDimension($rowIdx)->setRowHeight(20);
    $rowIdx++;
//}

}

$objPHPExcel->getActiveSheet()->getStyle(sprintf("D%s:D%s", 4, $rowIdx-1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aaf9f9f9");
$objPHPExcel->getActiveSheet()->getStyle(sprintf("F%s:F%s", 4, $rowIdx-1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aaf2f2f2");
$objPHPExcel->getActiveSheet()->getStyle(sprintf("H%s:H%s", 4, $rowIdx-1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aaf2f2f2");
$objPHPExcel->getActiveSheet()->getStyle(sprintf("J%s:J%s", 4, $rowIdx-1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aaf2f2f2");


//합계
$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($rowIdx)->setRowHeight(25);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("A%s", $rowIdx), '합 계');
$objPHPExcel->setActiveSheetIndex(0)->getStyle(sprintf("A%s", $rowIdx))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->setActiveSheetIndex(0)->getStyle(sprintf("A%s", $rowIdx))->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);


$objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("B%s", $rowIdx), sprintf("=SUM(B%s:B%s)", $rowIdx_start, $rowIdx-1));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("C%s", $rowIdx), sprintf("=SUM(C%s:C%s)", $rowIdx_start, $rowIdx-1));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("E%s", $rowIdx), sprintf("=SUM(E%s:E%s)", $rowIdx_start, $rowIdx-1));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("G%s", $rowIdx), sprintf("=SUM(G%s:G%s)", $rowIdx_start, $rowIdx-1));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("I%s", $rowIdx), sprintf("=SUM(I%s:I%s)", $rowIdx_start, $rowIdx-1));
$objPHPExcel->setActiveSheetIndex(0)->getStyle(sprintf("B%s:J%s", $rowIdx, $rowIdx))->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);

//percentage
$objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("D%s", $rowIdx), sprintf("=C%s/B%s", $rowIdx, $rowIdx));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("F%s", $rowIdx), sprintf("=E%s/B%s", $rowIdx, $rowIdx));
if ($cancellation_cnt > 0) {
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("H%s", $rowIdx), sprintf("=G%s/E%s", $rowIdx, $rowIdx));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("J%s", $rowIdx), sprintf("=I%s/E%s", $rowIdx, $rowIdx));
} 
else {
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("H%s", $rowIdx), 0);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(sprintf("J%s", $rowIdx), 0);
}

$objPHPExcel->getActiveSheet()->getStyle(sprintf("B%s:B%s", $rowIdx_start, $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_ACCOUNTING);
$objPHPExcel->getActiveSheet()->getStyle(sprintf("C%s:C%s", $rowIdx_start, $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_ACCOUNTING);
$objPHPExcel->getActiveSheet()->getStyle(sprintf("E%s:E%s", $rowIdx_start, $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_ACCOUNTING);
$objPHPExcel->getActiveSheet()->getStyle(sprintf("G%s:G%s", $rowIdx_start, $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_ACCOUNTING);
$objPHPExcel->getActiveSheet()->getStyle(sprintf("I%s:I%s", $rowIdx_start, $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_ACCOUNTING);

$objPHPExcel->getActiveSheet()->getStyle(sprintf("D%s:D%s", $rowIdx_start, $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_PERCENTAGE);
$objPHPExcel->getActiveSheet()->getStyle(sprintf("F%s:F%s", $rowIdx_start, $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_PERCENTAGE);
$objPHPExcel->getActiveSheet()->getStyle(sprintf("H%s:H%s", $rowIdx_start, $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_PERCENTAGE);
$objPHPExcel->getActiveSheet()->getStyle(sprintf("J%s:J%s", $rowIdx_start, $rowIdx))->getNumberFormat()->setFormatCode(FORMAT_CODE_PERCENTAGE);

$objPHPExcel->getActiveSheet()->getStyle(sprintf("A%s:B%s", $rowIdx, $rowIdx))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aaC4D79B");
$objPHPExcel->getActiveSheet()->getStyle(sprintf("C%s:D%s", $rowIdx, $rowIdx))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aaccc0da");
$objPHPExcel->getActiveSheet()->getStyle(sprintf("E%s:F%s", $rowIdx, $rowIdx))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aa8db4e2");
$objPHPExcel->getActiveSheet()->getStyle(sprintf("G%s:J%s", $rowIdx, $rowIdx))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("aab8cce4");

$objPHPExcel->getActiveSheet()->getStyle(sprintf("A%s:J%s", $rowIdx, $rowIdx))->applyFromArray(array(
    'font'  => array(
        'bold'  => true
    )
));

$objPHPExcel->getActiveSheet()->getStyle(sprintf("A%s:J%s", 1, $rowIdx))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A1:J3')->applyFromArray(array(
    'font'  => array(
        'bold'  => true
    ),
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
    )   
));

$objPHPExcel->getActiveSheet()->getStyle(sprintf("A%s:J%s", $rowIdx, $rowIdx))->applyFromArray(array(
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
    )
));

$objPHPExcel->getActiveSheet()->getStyle(sprintf("A%s:J%s", 1, $rowIdx))->applyFromArray(array(
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
    )
));


// create file name
$fileName = $year."년".$month."월_CS반품교환마감";

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