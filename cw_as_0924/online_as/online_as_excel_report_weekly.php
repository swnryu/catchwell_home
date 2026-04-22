<?
session_start();

include ("../common.php");
require ("../check_session.php");
include ("../def_inc.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';

//header("Content-type: text/html; charset=utf-8");


$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : "";
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : "";
$search_item = isset($_GET['search_item']) ? $_GET['search_item'] : "";
$search_order = isset($_GET['search_order']) ? $_GET['search_order'] : "";

$year = date_format(date_create($date_to), "Y");
$month = date_format(date_create($date_to), "m");
$month_from = sprintf("%d-%02d-01", $year, $month);
$month_to = $date_to;

$template_file = "template/as_weekly_report_template.xlsx";
$from = preg_replace("/[^0-9]*/s", "", $date_from);
$to = preg_replace("/[^0-9]*/s", "", $date_to);
$target_file = "AS주간보고_".$from."_".$to."_n.xlsx";



$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load($template_file);

$sheet = $objPHPExcel->getActiveSheet();

$sheet->getColumnDimension('B')->setWidth(9);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(24);
$sheet->setCellValue("B3", sprintf("주간 현황 (%s ~ %s)", $date_from, $date_to));
$sheet->freezePane('B5');
$sheet->setCellValue("F3", sprintf("%s월 누계", $month));


$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));
$month_to2 = date("Y-m-d", strtotime($month_to." +1 day"));
$cur_row = 5;



include('../product_category_inc.php');
$arr_product_ex = array();
for($i=0;$i<count($arr_as_model);$i++)
{
    $arr_product_ex = array_merge($arr_product_ex, $arr_as_model[$i]);
}

for($i=0;$i<count($arr_product_ex);$i++) {

    $arr_memo = array();
    for($k=0;$k<count($arr_fix_desc);$k++)
    {
        if (strpos($arr_fix_desc[$k][0], $arr_product_ex[$i]) !== false)
        {
            for($y=1;$y<count($arr_fix_desc[$k]);$y++)
            {
                $arr_memo = array_merge($arr_memo, array($arr_fix_desc[$k][$y]." 수리"));
                $arr_memo = array_merge($arr_memo, array($arr_fix_desc[$k][$y]." 교체"));
            }
            $arr_memo = array_merge($arr_memo, array("[ETC]"));
            break;
        }
    }
    if (count($arr_memo) <= 0)
    {
        $arr_memo = array_merge($arr_fix_desc[$arr_fix_desc_unknown], array("[ETC]"));
        array_shift($arr_memo);
    }

    $sheet->setCellValue(sprintf("B%s", $cur_row), $arr_product_ex[$i] );
    $sheet->mergeCells(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo)));
    $sheet->getStyle(sprintf("B%s", $cur_row))->getAlignment()->setWrapText(true);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo)))->getFont()->setBold(true);
    
    $start_row = $cur_row;
    $sum=0; $sum_monthly=0; $sum_total=0;

    for($j=0;$j<count($arr_memo);$j++) {

        $memo = $arr_memo[$j];
        $memo = str_replace("(V)","",$memo);
        $memo = str_replace("[ETC]","[기타]",$memo);
        $sheet->setCellValue(sprintf("D%s", $cur_row), $memo);

        $state = constant('ST_AS_COMPLETED');
        $query = "select t1.weekly_cnt, t2.monthly_cnt, t3.total_cnt from ";
        $query .= "(select count(*) as weekly_cnt from as_parcel_service where (product_name='$arr_product_ex[$i]') and process_state = $state and (admin_memo like '%$arr_memo[$j]%') and (update_time between date('$date_from') and date('$date_to2')) ) as t1, ";
        $query .= "(select count(*) as monthly_cnt from as_parcel_service where (product_name='$arr_product_ex[$i]') and process_state = $state and (admin_memo like '%$arr_memo[$j]%') and (update_time between date('$month_from') and date('$month_to2')) ) as t2, ";
        $query .= "(select count(*) as total_cnt from as_parcel_service where (product_name='$arr_product_ex[$i]') and process_state = $state and (admin_memo like '%$arr_memo[$j]%') and (date(update_time) < date('$month_to2')) ) as t3 ";

        $result = mysqli_query($db->db_conn, $query);
        if ($result != false) {
            $row = mysqli_fetch_array($result);
    
            //weekly
            $sheet->setCellValue(sprintf("E%s", $cur_row), $row['weekly_cnt'] );
            $sum += $row['weekly_cnt'];

            //monthly
            $sheet->setCellValue(sprintf("F%s", $cur_row), $row['monthly_cnt'] );
            $sum_monthly += $row['monthly_cnt'];

            //Total
            $sheet->setCellValue(sprintf("G%s", $cur_row), $row['total_cnt'] );
            $sum_total += $row['total_cnt'];
        } else {
            $sheet->setCellValue(sprintf("H%s", $cur_row), "error" );
        }

        $cur_row++;
    }
  
    //합계
    $sheet->setCellValue(sprintf("C%s", $cur_row), "합 계");
    $sheet->mergeCells(sprintf("C%s:D%s", $cur_row, $cur_row));
    $sheet->setCellValue(sprintf("E%s", $cur_row), $sum);
    $sheet->setCellValue(sprintf("F%s", $cur_row), $sum_monthly);
    $sheet->setCellValue(sprintf("G%s", $cur_row), $sum_total);

    $sheet->getStyle(sprintf("C%s:D%s", $cur_row, $cur_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle(sprintf("C%s:D%s", $cur_row, $cur_row))->getFont()->setBold(true);

    $sheet->getStyle(sprintf("B%s:G%s", $start_row, $cur_row))->applyFromArray(array(
        'borders' => array(
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        )));  

    $sheet->getStyle(sprintf("C%s:G%s", $cur_row, $cur_row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFdddddd');

    unset($arr_memo);
    $cur_row++;
}


//$cur_row++;

//wirte file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="'.$target_file.'"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');


$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//$objWriter->save('php://output');
$objWriter->save("../temp/".$target_file);

// disconnect
$objPHPExcel->disconnectWorksheets();
$objPHPExcel->garbageCollect();

unset( $objWriter, $objPHPExcel );


$result = array('success' => 'ok', 'name' => $target_file);
echo json_encode($result, JSON_FORCE_OBJECT);

?>