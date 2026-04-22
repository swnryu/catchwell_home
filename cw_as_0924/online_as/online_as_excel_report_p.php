<?//session_name("CW_AS");
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
$target_file = "AS주간보고_".$from."_".$to.".xlsx";



$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load($template_file);

$sheet = $objPHPExcel->getActiveSheet();

$sheet->getColumnDimension('B')->setWidth(9);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(24);
$sheet->setCellValue("B3", sprintf("주간 현황 (%s ~ %s)", $date_from, $date_to));
$sheet->freezePane('B5');
$sheet->setCellValue("F3", sprintf("%s월 누계", $month));

//
$arr_memo_vc = array_merge($arr_admin_memo_vc, array("[ETC]"));
$arr_memo_rc = array_merge($arr_admin_memo_rc, array("[ETC]"));
$arr_memo_hm = array_merge($arr_admin_memo_hm, array("[ETC]"));
$arr_memo_hs = array_merge($arr_admin_memo_hs, array("[ETC]"));
$arr_memo_mc = array_merge($arr_admin_memo_mc, array("[ETC]"));

//$arr_product_vc_ex = array_merge($arr_product_vc, array("CV6(Plus)","CV8(Light)","C21(Pro)"));
$arr_product_vc_ex = array_merge($arr_product_vc);

$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));
$month_to2 = date("Y-m-d", strtotime($month_to." +1 day"));


$cur_row = 5;
for($i=0;$i<count($arr_product_vc_ex);$i++) {//무선청소기

    $sheet->setCellValue(sprintf("B%s", $cur_row), $arr_product_vc_ex[$i] );
    $sheet->mergeCells(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_vc)));
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_vc)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_vc)))->getFont()->setBold(true);
    
    $start_row = $cur_row;
    $sum=0; $sum_monthly=0; $sum_total=0;

    for($j=0;$j<count($arr_memo_vc);$j++) {
        $memo = $arr_memo_vc[$j];
        $memo = str_replace("(V)","",$memo);
        $memo = str_replace("[ETC]","[기타]",$memo);
        $sheet->setCellValue(sprintf("D%s", $cur_row), $memo /*$arr_memo_vc[$j]*/ );

        //20210126
        $state = constant('ST_AS_COMPLETED');
        $query = "select t1.weekly_cnt, t2.monthly_cnt, t3.total_cnt from ";
        $query .= "(select count(*) as weekly_cnt from as_parcel_service where (product_name='$arr_product_vc_ex[$i]') and process_state = $state and (admin_memo like '%$arr_memo_vc[$j]%') and (update_time between date('$date_from') and date('$date_to2')) ) as t1, ";
        $query .= "(select count(*) as monthly_cnt from as_parcel_service where (product_name='$arr_product_vc_ex[$i]') and process_state = $state and (admin_memo like '%$arr_memo_vc[$j]%') and (update_time between date('$month_from') and date('$month_to2')) ) as t2, ";
        $query .= "(select count(*) as total_cnt from as_parcel_service where (product_name='$arr_product_vc_ex[$i]') and process_state = $state and (admin_memo like '%$arr_memo_vc[$j]%') and (date(update_time) < date('$month_to2')) ) as t3 ";

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
            //$sheet->setCellValue(sprintf("H%s", $cur_row), $query );
            $sheet->setCellValue(sprintf("H%s", $cur_row), "error" );
        }

/*        
        //weekly
        $where = "where (update_time between date('$date_from') and (date('$date_to2'))) and (product_name = '$arr_product_vc_ex[$i]') and (admin_memo like '%$arr_memo_vc[$j]%') ";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("E%s", $cur_row), $cnt );
        $sum += $cnt;

        //monthly
        $where = "where (update_time between date('$month_from') and (date('$month_to2'))) and (product_name = '$arr_product_vc_ex[$i]') and (admin_memo like '%$arr_memo_vc[$j]%') ";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt_monthly = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("F%s", $cur_row), $cnt_monthly );
        $sum_monthly += $cnt_monthly;

        //Total
        $where = "where (product_name = '$arr_product_vc_ex[$i]') and (admin_memo like '%$arr_memo_vc[$j]%') ";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt_total = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("G%s", $cur_row), $cnt_total );
        $sum_total += $cnt_total;
*/
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

    $cur_row++;
}

for($i=0;$i<count($arr_product_rc);$i++) {//로봇청소기

    $start_row = $cur_row;
    $sheet->setCellValue(sprintf("B%s", $cur_row), $arr_product_rc[$i] );
    $sheet->mergeCells(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_rc)));
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_rc)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_rc)))->getFont()->setBold(true);

    $sum=0; $sum_monthly=0; $sum_total=0;
    
    for($j=0;$j<count($arr_memo_rc);$j++) {
        $memo = $arr_memo_rc[$j];
        $memo = str_replace("(R)","",$memo);
        $memo = str_replace("[ETC]","[기타]",$memo);
        $sheet->setCellValue(sprintf("D%s", $cur_row), $memo /*$arr_memo_rc[$j]*/ );

        //20210126
        $state = constant('ST_AS_COMPLETED');
        $query = "select t1.weekly_cnt, t2.monthly_cnt, t3.total_cnt from ";
        $query .= "(select count(*) as weekly_cnt from as_parcel_service where (product_name='$arr_product_rc[$i]') and process_state = $state and (admin_memo like '%$arr_memo_rc[$j]%') and (update_time between date('$date_from') and date('$date_to2')) ) as t1, ";
        $query .= "(select count(*) as monthly_cnt from as_parcel_service where (product_name='$arr_product_rc[$i]') and process_state = $state and (admin_memo like '%$arr_memo_rc[$j]%') and (update_time between date('$month_from') and date('$month_to2')) ) as t2, ";
        $query .= "(select count(*) as total_cnt from as_parcel_service where (product_name='$arr_product_rc[$i]') and process_state = $state and (admin_memo like '%$arr_memo_rc[$j]%') and (date(update_time) < date('$month_to2')) ) as t3 ";

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
            //$sheet->setCellValue(sprintf("H%s", $cur_row), $query );
            $sheet->setCellValue(sprintf("H%s", $cur_row), "error" );
        }

/*
        //weekly
        $where = "where (update_time between date('$date_from') and (date('$date_to2'))) and (product_name = '$arr_product_rc[$i]') and (admin_memo like '%$arr_memo_rc[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("E%s", $cur_row), $cnt );
        $sum += $cnt;

        //monthly
        $where = "where (update_time between date('$month_from') and (date('$month_to2'))) and (product_name = '$arr_product_rc[$i]') and (admin_memo like '%$arr_memo_rc[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt_monthly = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("F%s", $cur_row), $cnt_monthly );
        $sum_monthly += $cnt_monthly;

        //Total
        $where = "where (product_name = '$arr_product_rc[$i]') and (admin_memo like '%$arr_memo_rc[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt_total = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("G%s", $cur_row), $cnt_total );
        $sum_total += $cnt_total;
*/
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

    $cur_row++;
}

//$arr_product_etc = array("CM7","SECRET01 블랙","SECRET01 화이트/핑크/민트", "CH200","기타");
//arr_product_etc[3]=> CH200 (가습기)
{
    $start_row = $cur_row;
    $sheet->setCellValue(sprintf("B%s", $cur_row), $arr_product_etc[3]."\n(가습기)");
    $sheet->mergeCells(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hm)));
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hm)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hm)))->getFont()->setBold(true);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hm)))->getAlignment()->setWrapText(true); 

    $sum=0; $sum_monthly=0; $sum_total=0;

    for($j=0;$j<count($arr_memo_hm);$j++) {
        $memo = $arr_memo_hm[$j];
        $memo = str_replace("(H)","",$memo);
        $memo = str_replace("[ETC]","[기타]",$memo);
        $sheet->setCellValue(sprintf("D%s", $cur_row), $memo /*$arr_memo_hm[$j]*/ );

        //20210126
        $state = constant('ST_AS_COMPLETED');
        $query = "select t1.weekly_cnt, t2.monthly_cnt, t3.total_cnt from ";
        $query .= "(select count(*) as weekly_cnt from as_parcel_service where (product_name='$arr_product_etc[3]') and process_state = $state and (admin_memo like '%$arr_memo_hm[$j]%') and (update_time between date('$date_from') and date('$date_to2')) ) as t1, ";
        $query .= "(select count(*) as monthly_cnt from as_parcel_service where (product_name='$arr_product_etc[3]') and process_state = $state and (admin_memo like '%$arr_memo_hm[$j]%') and (update_time between date('$month_from') and date('$month_to2')) ) as t2, ";
        $query .= "(select count(*) as total_cnt from as_parcel_service where (product_name='$arr_product_etc[3]') and process_state = $state and (admin_memo like '%$arr_memo_hm[$j]%') and (date(update_time) < date('$month_to2')) ) as t3 ";

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
            //$sheet->setCellValue(sprintf("H%s", $cur_row), $query );
            $sheet->setCellValue(sprintf("H%s", $cur_row), "error" );
        }

/*        
        //weekly
        $where = "where (update_time between date('$date_from') and (date('$date_to2'))) and (product_name = '$arr_product_etc[3]') and (admin_memo like '%$arr_memo_hm[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("E%s", $cur_row), $cnt );
        $sum += $cnt;

        //monthly
        $where = "where (update_time between date('$month_from') and (date('$month_to2'))) and (product_name = '$arr_product_etc[3]') and (admin_memo like '%$arr_memo_hm[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt_monthly = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("F%s", $cur_row), $cnt_monthly );
        $sum_monthly += $cnt_monthly;

        //total
        $where = "where (product_name = '$arr_product_etc[3]') and (admin_memo like '%$arr_memo_hm[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt_total = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("G%s", $cur_row), $cnt_total );
        $sum_total += $cnt_total;
*/
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

    $cur_row++;
}


//arr_product_etc[1]=> SECRET01 블랙
//arr_product_etc[2]=> SECRET01 화이트/핑크/민트
for($i=1;$i<3;$i++) {//고데기 

    $name=str_replace(" ", "\n", $arr_product_etc[$i]);
    $start_row = $cur_row;
    $sheet->setCellValue(sprintf("B%s", $cur_row), $name /*$arr_product_etc[$i]*/."\n(고데기)" );
    $sheet->mergeCells(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hs)));
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hs)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hs)))->getAlignment()->setWrapText(true);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hs)))->getFont()->setBold(true);

    $sum=0; $sum_monthly=0; $sum_total=0;

    for($j=0;$j<count($arr_memo_hs);$j++) {
        $memo = $arr_memo_hs[$j];
        $memo = str_replace("(S)","",$memo);
        $memo = str_replace("[ETC]","[기타]",$memo);
        $sheet->setCellValue(sprintf("D%s", $cur_row), $memo /*$arr_memo_hs[$j]*/ );

        //20210126
        $state = constant('ST_AS_COMPLETED');
        $query = "select t1.weekly_cnt, t2.monthly_cnt, t3.total_cnt from ";
        $query .= "(select count(*) as weekly_cnt from as_parcel_service where (product_name='$arr_product_etc[$i]') and process_state = $state and (admin_memo like '%$arr_memo_hs[$j]%') and (update_time between date('$date_from') and date('$date_to2')) ) as t1, ";
        $query .= "(select count(*) as monthly_cnt from as_parcel_service where (product_name='$arr_product_etc[$i]') and process_state = $state and (admin_memo like '%$arr_memo_hs[$j]%') and (update_time between date('$month_from') and date('$month_to2')) ) as t2, ";
        $query .= "(select count(*) as total_cnt from as_parcel_service where (product_name='$arr_product_etc[$i]') and process_state = $state and (admin_memo like '%$arr_memo_hs[$j]%') and (date(update_time) < date('$month_to2')) ) as t3 ";

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
            //$sheet->setCellValue(sprintf("H%s", $cur_row), $query );
            $sheet->setCellValue(sprintf("H%s", $cur_row), "error" );
        }
/*        
        //weekly
        $where = "where (update_time between date('$date_from') and (date('$date_to2'))) and (product_name = '$arr_product_etc[$i]') and (admin_memo like '%$arr_memo_hs[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("E%s", $cur_row), $cnt );
        $sum += $cnt;

        //monthly
        $where = "where (update_time between date('$month_from') and (date('$month_to2'))) and (product_name = '$arr_product_etc[$i]') and (admin_memo like '%$arr_memo_hs[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt_monthly = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("F%s", $cur_row), $cnt_monthly );
        $sum_monthly += $cnt_monthly;

        //total
        $where = "where (product_name = '$arr_product_etc[$i]') and (admin_memo like '%$arr_memo_hs[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt_total = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("G%s", $cur_row), $cnt_total );
        $sum_total += $cnt_total;
*/
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

    $cur_row++;
}

//물걸레청소기
//arr_product_etc[0]=> CM7
{
    $start_row = $cur_row;
    $sheet->setCellValue(sprintf("B%s", $cur_row), $arr_product_etc[0]."\n(물걸레)" );
    $sheet->mergeCells(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_mc)));
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_mc)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_mc)))->getFont()->setBold(true);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_mc)))->getAlignment()->setWrapText(true); 

    $sum=0; $sum_monthly=0; $sum_total=0;

    for($j=0;$j<count($arr_memo_mc);$j++) {
        $memo = $arr_memo_mc[$j];
        $memo = str_replace("(M)","",$memo);
        $memo = str_replace("[ETC]","[기타]",$memo);
        $sheet->setCellValue(sprintf("D%s", $cur_row), $memo  ); //$arr_memo_mc[$j]

        //20210126
        $state = constant('ST_AS_COMPLETED');
        $query = "select t1.weekly_cnt, t2.monthly_cnt, t3.total_cnt from ";
        $query .= "(select count(*) as weekly_cnt from as_parcel_service where (product_name='$arr_product_etc[0]') and process_state = $state and (admin_memo like '%$arr_memo_mc[$j]%') and (update_time between date('$date_from') and date('$date_to2')) ) as t1, ";
        $query .= "(select count(*) as monthly_cnt from as_parcel_service where (product_name='$arr_product_etc[0]') and process_state = $state and (admin_memo like '%$arr_memo_mc[$j]%') and (update_time between date('$month_from') and date('$month_to2')) ) as t2, ";
        $query .= "(select count(*) as total_cnt from as_parcel_service where (product_name='$arr_product_etc[0]') and process_state = $state and (admin_memo like '%$arr_memo_mc[$j]%') and (date(update_time) < date('$month_to2')) ) as t3 ";

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
            //$sheet->setCellValue(sprintf("H%s", $cur_row), $query );
            $sheet->setCellValue(sprintf("H%s", $cur_row), "error" );
        }        
/*        
        //weekly
        $where = "where (update_time between date('$date_from') and (date('$date_to2'))) and (product_name = '$arr_product_etc[0]') and (admin_memo like '%$arr_memo_mc[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("E%s", $cur_row), $cnt );
        $sum += $cnt;

        //monthly
        $where = "where (update_time between date('$month_from') and (date('$month_to2'))) and (product_name = '$arr_product_etc[0]') and (admin_memo like '%$arr_memo_mc[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt_monthly = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("F%s", $cur_row), $cnt_monthly );
        $sum_monthly += $cnt_monthly;

        //total
        $where = "where (product_name = '$arr_product_etc[0]') and (admin_memo like '%$arr_memo_mc[$j]%')";
        $where = $where . "and process_state=". ST_AS_COMPLETED;
        $cnt_tatal = $db->cnt("as_parcel_service", $where);
        $sheet->setCellValue(sprintf("G%s", $cur_row), $cnt_tatal );
        $sum_total += $cnt_tatal;
*/
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

}




//arr_product_etc[4]=> 스나이퍼CG-72
//arr_product_etc[5]=> CW44
$cur_row++;
for($i=4;$i<6;$i++) {//기타

    $name=str_replace(" ", "\n", $arr_product_etc[$i]);
    $start_row = $cur_row;
    $sheet->setCellValue(sprintf("B%s", $cur_row), $name /*$arr_product_etc[$i]*/."\n" );
    $sheet->mergeCells(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hs)));
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hs)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hs)))->getAlignment()->setWrapText(true);
    $sheet->getStyle(sprintf("B%s:B%s", $cur_row, $cur_row+count($arr_memo_hs)))->getFont()->setBold(true);

    $sum=0; $sum_monthly=0; $sum_total=0;

    for($j=0;$j<count($arr_memo_hs);$j++) {
        $memo = $arr_memo_hs[$j];
        $memo = str_replace("(S)","",$memo);
        $memo = str_replace("[ETC]","[기타]",$memo);
        $sheet->setCellValue(sprintf("D%s", $cur_row), $memo /*$arr_memo_hs[$j]*/ );

        //20210126
        $state = constant('ST_AS_COMPLETED');
        $query = "select t1.weekly_cnt, t2.monthly_cnt, t3.total_cnt from ";
        $query .= "(select count(*) as weekly_cnt from as_parcel_service where (product_name='$arr_product_etc[$i]') and process_state = $state and (admin_memo like '%$arr_memo_hs[$j]%') and (update_time between date('$date_from') and date('$date_to2')) ) as t1, ";
        $query .= "(select count(*) as monthly_cnt from as_parcel_service where (product_name='$arr_product_etc[$i]') and process_state = $state and (admin_memo like '%$arr_memo_hs[$j]%') and (update_time between date('$month_from') and date('$month_to2')) ) as t2, ";
        $query .= "(select count(*) as total_cnt from as_parcel_service where (product_name='$arr_product_etc[$i]') and process_state = $state and (admin_memo like '%$arr_memo_hs[$j]%') and (date(update_time) < date('$month_to2')) ) as t3 ";

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
            //$sheet->setCellValue(sprintf("H%s", $cur_row), $query );
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

    $cur_row++;
}



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