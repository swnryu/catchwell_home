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

include ("../product_category_inc.php");

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : "";
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : "";
$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));

$year = date_format(date_create($date_to), "Y");
$month = date_format(date_create($date_to), "m");
$day = date_format(date_create($date_to), "d");

$rptype=isset($_GET['rptype']) ? $_GET['rptype'] : "daily";

$table = "cs_inbound_call";

$arr_admin_name = array();

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

$sheet->getDefaultRowDimension()->setRowHeight(17);
$sheet->getRowDimension(1)->setRowHeight(40);
$sheet->getRowDimension(2)->setRowHeight(18);
$sheet->getRowDimension(3)->setRowHeight(18);

//$sheet->setCellValue("A1", $year."년 ".$month."월 ".$day."일 "."CS고객응대 일간 현황");
if ($rptype=="daily"){
    $sheet->setCellValue("A1", "CS 고객응대 현황 (일일보고)");
} else if ($rptype=="weekly"){
    $sheet->setCellValue("A1", "CS 고객응대 현황 (주간보고)");
} else if ($rptype=="monthly"){
    $sheet->setCellValue("A1", "CS 고객응대 현황 (월간보고)");
}


$sheet->mergeCells('A2:A3')->setCellValue('A2', "제품명");
$sheet->mergeCells('B2:B3')->setCellValue('B2', "문의유형");

//$sheet->mergeCells('A1:F1')->setCellValue('A1', $year."년 ".$month."월".$day."일 "."CS 전화 문의 응대 현황");
//$sheet->mergeCells('C2:F2')->setCellValue('C2', $date_to);

$sheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getDefaultStyle()->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);

$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(20);
//$sheet->getColumnDimension('C')->setWidth(7);
//$sheet->getColumnDimension('D')->setWidth(7);
//$sheet->getColumnDimension('E')->setWidth(7);
//$sheet->getColumnDimension('F')->setWidth(7);


$arr_total_cnt = array(); 

//query pic_name
$query = "select pic_name FROM $table WHERE reg_datetime BETWEEN '$date_from' AND '$date_to2' GROUP BY pic_name ORDER BY pic_name ASC";
$rs	= mysqli_query($db->db_conn, $query);
$x=0;
while( $row = mysqli_fetch_array( $rs ) ) 
{
    array_push($arr_admin_name, $row['pic_name']);
    array_push($arr_total_cnt, array(0,0,0,0,0,0));
}
array_push($arr_admin_name, "합 계");
mysqli_free_result($rs);

for ($x=0;$x<count($arr_admin_name);$x++)
{
    //$sheet->mergeCells(sprintf("%c2:%c3", 67+$x, 67+$x))->setCellValue(sprintf("%c2", 67+$x), $arr_admin_name[$x]);
    $sheet->setCellValue(sprintf("%c3", 67+$x), $arr_admin_name[$x]);
    $sheet->getColumnDimension(sprintf("%c", 67+$x))->setWidth(7);
}
$sheet->mergeCells(sprintf("%c1:%c1", 65, 65+count($arr_admin_name)+1));

if ($rptype=="daily"){
    $sheet->mergeCells(sprintf("C2:%c2", 66+count($arr_admin_name)))->setCellValue("C2", sprintf("%s-%s-%s", $year, $month, $day));
} else if ($rptype=="weekly"){
    $sheet->mergeCells(sprintf("C2:%c2", 66+count($arr_admin_name)))->setCellValue("C2", sprintf("%s ~ %s", date("Y년m월d일", strtotime($date_from)), date("Y년m월d일", strtotime($date_to))) );
} else if ($rptype=="monthly"){
    $sheet->mergeCells(sprintf("C2:%c2", 66+count($arr_admin_name)))->setCellValue("C2", sprintf("%s ~ %s", date("Y년m월d일", strtotime($date_from)), date("Y년m월d일", strtotime($date_to))) );
}

$cnt_type = count($arr_inbound_call_type);
$cnt_name = count($arr_admin_name);



//title border
$sheet->getStyle(sprintf("%c1:%c1", 65, 65+$cnt_name+1))->applyFromArray(array(
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
    ),
    'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => 'FFFFFF'),
        'size'  => 14,
        'name'  => '맑은 고딕'
    ),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => '4f81bd')
    )
));
//sub-title border
$sheet->getStyle(sprintf("%c2:%c3", 65, 66+$cnt_name))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            ),
            'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE),
        ),
        'font'  => array(
            'bold'  => true,
            'color' => array('rgb' => '000000'),
            'size'  => 10,
            'name'  => '맑은 고딕'
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => '8db4e2')
        )
    )
);



//query call count
$lastIndex = count($arr_as_model);
$arr_product_all = array_merge($arr_as_model, array($lastIndex =>array("전체 합계")));


$isSum = false;
$rowIdx = 4;
for ($i=0; $i < count($arr_product_all); $i++) 
{
    for ($j=0; $j < count($arr_product_all[$i]); $j++) 
    {
        $product = $arr_product_all[$i][$j];
        
        if ( ($i==count($arr_product_all)-1) && ($j==count($arr_product_all[$i])-1) )
        {
            $isSum = true;
            //echo $isSum;
            //exit;
        }

//echo $product."<br>";

        $sheet->mergeCells(sprintf("A%d:A%d", $rowIdx, $rowIdx+$cnt_type-1))->setCellValue(sprintf("A%d", $rowIdx), $product);
        $sheet->getStyle(sprintf("A%d:A%d", $rowIdx, $rowIdx+$cnt_type-1))->getAlignment()->setWrapText(true); 

        $sheet->setCellValue(sprintf("B%d", $rowIdx),   $arr_inbound_call_type[1]);
        $sheet->setCellValue(sprintf("B%d", $rowIdx+1), $arr_inbound_call_type[2]);
        $sheet->setCellValue(sprintf("B%d", $rowIdx+2), $arr_inbound_call_type[3]);
        $sheet->setCellValue(sprintf("B%d", $rowIdx+3), $arr_inbound_call_type[4]);
        $sheet->setCellValue(sprintf("B%d", $rowIdx+4), $arr_inbound_call_type[5]);
        $sheet->setCellValue(sprintf("B%d", $rowIdx+5), $arr_inbound_call_type[6]);
        $sheet->setCellValue(sprintf("B%d", $rowIdx+6), "합 계");

        $sheet->setCellValue(sprintf("%c%d", 66+$cnt_name, $rowIdx), sprintf("=SUM(%c%d:%c%d)", 67, $rowIdx, 67+$cnt_name-2, $rowIdx) );
        $sheet->setCellValue(sprintf("%c%d", 66+$cnt_name, $rowIdx+1), sprintf("=SUM(%c%d:%c%d)", 67, $rowIdx+1, 67+$cnt_name-2, $rowIdx+1) );
        $sheet->setCellValue(sprintf("%c%d", 66+$cnt_name, $rowIdx+2), sprintf("=SUM(%c%d:%c%d)", 67, $rowIdx+2, 67+$cnt_name-2, $rowIdx+2) );
        $sheet->setCellValue(sprintf("%c%d", 66+$cnt_name, $rowIdx+3), sprintf("=SUM(%c%d:%c%d)", 67, $rowIdx+3, 67+$cnt_name-2, $rowIdx+3) );
        $sheet->setCellValue(sprintf("%c%d", 66+$cnt_name, $rowIdx+4), sprintf("=SUM(%c%d:%c%d)", 67, $rowIdx+4, 67+$cnt_name-2, $rowIdx+4) );
        $sheet->setCellValue(sprintf("%c%d", 66+$cnt_name, $rowIdx+5), sprintf("=SUM(%c%d:%c%d)", 67, $rowIdx+5, 67+$cnt_name-2, $rowIdx+5) );
        

        //합계 border - 가로
        $sheet->getStyle(sprintf("B%d:%c%d", $rowIdx+6, 66+$cnt_name, $rowIdx+6))->applyFromArray(array(
            'borders'  => array(
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            )
        ));
        //합계 border - 세로
        $sheet->getStyle(sprintf("%c%d:%c%d", 66+$cnt_name, $rowIdx, 66+$cnt_name, $rowIdx+6))->applyFromArray(array(
            'borders'  => array(
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            )
        ));

        $col_idx=0;
        for ($n=0; $n < count($arr_admin_name)-1; $n++) 
        {
            if ($isSum)
            {
                $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx),   $arr_total_cnt[$n][1] ); //C,D,E,F
                $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+1), $arr_total_cnt[$n][2] );
                $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+2), $arr_total_cnt[$n][3] );
                $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+3), $arr_total_cnt[$n][4] );
                $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+4), $arr_total_cnt[$n][5] );
                $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+5), $arr_total_cnt[$n][6] );
                
            }
            else 
            {
                $name = $arr_admin_name[$n];

                //query call count 
                $query = "select product_name, inquiry_type, pic_name, COUNT(pic_name) as cnt FROM $table WHERE reg_datetime BETWEEN '$date_from' AND '$date_to2' AND product_name='$product' and pic_name='$name' GROUP BY product_name, inquiry_type, pic_name ORDER BY product_name, inquiry_type";
                $rs	= mysqli_query($db->db_conn, $query);

    //echo $query."<br>";
    //$sheet->setCellValue(sprintf("%c%d",75+$n, $rowIdx), $query);//TEST

                while ($row = mysqli_fetch_array( $rs ))
                {
                    $type_val = $row['cnt'];
                    $arr_total_cnt[$n][$row['inquiry_type']] += $type_val;

                    switch($row['inquiry_type']) 
                    {
                        case 1:{
                            $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx), $type_val ); //C,D,E,F
                        }break;
                        case 2:{
                            $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+1), $type_val );
                        }break;
                        case 3:{
                            $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+2), $type_val );
                        }break;
                        case 4:{
                            $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+3), $type_val );
                        }break;
                        case 5:{
                            $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+4), $type_val );
                        }break;
                        case 6:{
                            $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+5), $type_val );
                        }break;
                    }
                }
//$sheet->setCellValue(sprintf("F%d",$rowIdx), sprintf("%c%d", 67+$idx, $rowIdx)); //TEST
                mysqli_free_result($rs);
            }

            //합계-관리자별
            $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+6), sprintf("=SUM(%c%d:%c%d)", 67+$col_idx, $rowIdx, 67+$col_idx, $rowIdx+5) );
            $col_idx++;
        }

        //합계-유형별
        $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+6), sprintf("=SUM(%c%d:%c%d)", 67+$col_idx, $rowIdx, 67+$col_idx, $rowIdx+5) );

        $sheet->getRowDimension($rowIdx)->setRowHeight(17);
        $sheet->getRowDimension($rowIdx+1)->setRowHeight(17);
        $sheet->getRowDimension($rowIdx+2)->setRowHeight(17);
        $sheet->getRowDimension($rowIdx+3)->setRowHeight(17);
        $sheet->getRowDimension($rowIdx+4)->setRowHeight(17);
        $sheet->getRowDimension($rowIdx+5)->setRowHeight(17);

        $sheet->getStyle(sprintf("A%d:%c%d", $rowIdx, 67+$col_idx, $rowIdx+6))->applyFromArray(array(
            'borders'  => array(
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            ),
        ));

        if ($isSum)
        {
            $sheet->getStyle(sprintf("B%d:%c%d", $rowIdx+6, 66+$col_idx+1, $rowIdx+6))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("d8e4bc");
            $sheet->getStyle(sprintf("A%d", $rowIdx))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("d8e4bc");
        }
        else{
            //$sheet->getStyle(sprintf("B%d:%c%d", $rowIdx+5, 66+$col_idx+1, $rowIdx+5))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("dce6f1");
            $sheet->getStyle(sprintf("A%d", $rowIdx))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("dce6f1");
        }

        //
        $sheet->getStyle(sprintf("A%d:%c%d", $rowIdx, 66+$cnt_name, $rowIdx+6))->applyFromArray(array(
            'borders'  => array(
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE),
            )
        ));

        $rowIdx = $rowIdx + $cnt_type;
    }
}

//border A,B cell
$sheet->getStyle(sprintf("A%d:A%d", 4, $rowIdx-1))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    )
);
$sheet->getStyle(sprintf("B%d:B%d", 4, $rowIdx-1))->applyFromArray(array(
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
    )
));

/*
//누계
$sheet->getRowDimension($rowIdx)->setRowHeight(17);
$sheet->getRowDimension($rowIdx+1)->setRowHeight(17);
$sheet->getRowDimension($rowIdx+2)->setRowHeight(17);
$sheet->mergeCells(sprintf("A%d:A%d", $rowIdx, $rowIdx+2))->setCellValue(sprintf("A%d", $rowIdx), "전체 누계");
$sheet->getStyle(sprintf("A%d:A%d", $rowIdx, $rowIdx+2))->getAlignment()->setWrapText(true); 

$sheet->setCellValue(sprintf("B%d", $rowIdx),   "콜백 누계");
$sheet->setCellValue(sprintf("B%d", $rowIdx+1), "문자 누계");
$sheet->setCellValue(sprintf("B%d", $rowIdx+2), "총 누계");

//콜백누계,문자누계
$sheet->setCellValue(sprintf("%c%d", 67+count($arr_admin_name)-1, $rowIdx), sprintf("=SUM(C%d:%c%d)",   $rowIdx,   66+count($arr_admin_name)-1, $rowIdx));
$sheet->setCellValue(sprintf("%c%d", 67+count($arr_admin_name)-1, $rowIdx+1), sprintf("=SUM(C%d:%c%d)", $rowIdx+1, 66+count($arr_admin_name)-1, $rowIdx+1));


$col_idx=0;
for ($n=0; $n < count($arr_admin_name); $n++) 
{
    //총계
    $sheet->setCellValue(sprintf("%c%d", 67+$col_idx, $rowIdx+2), sprintf("=SUM(%c%d:%c%d)", 67+$col_idx, $rowIdx-1, 67+$col_idx, $rowIdx+1) );
    $col_idx++;
}

//border
$sheet->getStyle(sprintf("A%d:A%d", $rowIdx, $rowIdx+2))->applyFromArray(array(
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE),
    ),
    'font'  => array(
        'bold'  => true,
    ),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'C4D79B')
    )    
));
$sheet->getStyle(sprintf("B%d:B%d", $rowIdx, $rowIdx+2))->applyFromArray(array(
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE),
    )
));
$sheet->getStyle(sprintf("C%d:%c%d", $rowIdx, 66+count($arr_admin_name)-1, $rowIdx+2))->applyFromArray(array(
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
    )
));
$sheet->getStyle(sprintf("%c%d:%c%d", 66+count($arr_admin_name), $rowIdx, 66+count($arr_admin_name), $rowIdx+2))->applyFromArray(array(
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
    )
));
$sheet->getStyle(sprintf("B%d:%c%d", $rowIdx+2, 66+count($arr_admin_name), $rowIdx+2))->applyFromArray(array(
    'borders'  => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
    ),
    'font'  => array(
        'bold'  => true,
    ),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'C4D79B')
    )
));
*/


//틀고정
$sheet->freezePane('A4');

// create file name 
if($rptype=="daily"){
    $sheet->setTitle("일일보고");
    $fileName = sprintf("CS고객응대_일일보고_%d%02d%02d", $year,$month,$day,$rptype);
}
else if($rptype=="weekly"){
    $sheet->setTitle("주간보고");
    $fileName = sprintf("CS고객응대_주간보고_%d%02d%02d", $year,$month,$day,$rptype);
}
else if($rptype=="monthly"){
    $sheet->setTitle("월간보고");
    $fileName = sprintf("CS고객응대_월간보고_%d%02d%02d", $year,$month,$day,$rptype);
}


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