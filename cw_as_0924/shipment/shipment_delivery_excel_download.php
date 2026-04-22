<?//session_name("CW_AS");
session_start();

include ("../common.php");
require ("../check_session.php");
include ("../def_inc.php");
include ("shipment_def.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';

const FORMAT_NUMBER_COMMA_SEPARATED3 = '#,##0';
const FORMAT_CODE_ACCOUNTING = '_-* #,##0_-;-* #,##0_-;_-* "-"_-;_-@_-';

$date = isset($_GET['date_today']) ? $_GET['date_today'] : "";
if ($date=="") {
    $tools->errMsg("출고일을 확인하세요."); 
    exit;
}


///////////////////////////////////////////////////////////////////////////
$table = 'delivery_package';
$package_size2 = array();
$package_price2 = array();

$query = "select * from $table where type=0 order by idx asc";
$result = mysqli_query($db->db_conn, $query);
while($row = mysqli_fetch_array($result)) {
    $package_price2 += array($row['box_size'] => $row['price']);
}

$query = "select * from $table where type=1 order by idx asc";
$result = mysqli_query($db->db_conn, $query);
while($row = mysqli_fetch_array($result)) {
    $package_size2 = array_merge($package_size2, array($row['model_name'] => $row['box_size']));
}

if (count($package_size2) == 0)
{
    $package_size2 = array_merge($package_size2, $package_size);
}


// create new PHPExcel object
$objPHPExcel = new PHPExcel();

$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
//$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setvERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);


$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(45);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);

$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('A1', '품목명')
->setCellValue('B1', '박스수량')
->setCellValue('C1', '이름')
->setCellValue('D1', '전화번호')
->setCellValue('E1', '전화번호2')
->setCellValue('F1', '주소')
->setCellValue('G1', '배송메시지')
->setCellValue('H1', '고객주문번호')   
->setCellValue('I1', '기본운임')
->setCellValue('J1', '박스타입');   

$objPHPExcel->getActiveSheet()
->getStyle('A1:J1')
->getFill()
->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
->getStartColor()
->setARGB('FFd0d0d0');



//select * from shipping_date_new where date = '2021-02-05' order by idx desc
//$where = "where date = '$date' and status=0 order by idx desc"; //TEST
$where = "where status=0 order by idx asc";
$rs = $db->select("shipping_date_new", $where);

$rs_cnt = mysqli_num_rows($rs);

$rowIdx = 2;
while( $row = mysqli_fetch_array( $rs ) ) {

   //20220105
   //전화번호 체크 (-없이 숫자만있고, 10으로 시작되는번호)
   $phone1 = $row['phone1'];
   $phone2 = $row['phone2'];
   if (strpos($phone1, "10")===0 && strpos($phone1, "-")===false)
   {
       $phone1 = '0' . $phone1;
       $phone1 = format_phone($phone1);
   }
   if (strpos($phone2, "10")===0 && strpos($phone2, "-")===false)
   {
       $phone2 = '0' . $phone2;
       $phone2 = format_phone($phone2);
   }

    
    $address = "";
    $addr = preg_split("/[\s,]+/", $row['address']);
    $address_first = $addr[0];

    //숫자, (숫자) 체크 
    if(preg_match("/^[0-9()]/i", $address_first)) {
        $address = str_replace($address_first, "", $row['address']);
        if (substr($address, 0, 1) == " ") {
            $address = substr($address, 1);
        }
    } else {
        $address = $row['address'];
    }

    $box_price = $package_price[2]; //default 중 size
    
    $model_name = str_replace("_새상품 재출고", "", $row['model']);
    $model_name = str_replace("_사은품", "", $model_name);
    $model_name = str_replace("_체험단", "", $model_name);
    $model_name = str_replace("_입사선물", "", $model_name);
    
    //20220118
    $box_type = $package_size2[$model_name]; 
    if ($box_type=="") {
        $box_type = '3'; //default 중 size
    }

    $box_price = $package_price2[$box_type];
    if ($box_price=='') {
        $box_price = $package_price[$box_type]; //default 중 size
    }
/*    
    if ($box_type=='1')       { $box_price = $package_price[0]; } 
    else if ($box_type=='2')  { $box_price = $package_price[1]; } 
    else if ($box_type=='3')  { $box_price = $package_price[2]; } 
    else if ($box_type=='4')  { $box_price = $package_price[3]; } 
    else if ($box_type=='5')  { $box_price = $package_price[5]; } 
    else if ($box_type=='7')  { $box_price = $package_price[4]; } //20220105
*/
    $objPHPExcel->getActiveSheet()->getStyle(sprintf("F%s:G%s", $rowIdx, $rowIdx))->getAlignment()->setWrapText(true);

    $objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue(sprintf("A%s", $rowIdx), $row['model'])
    ->setCellValue(sprintf("B%s", $rowIdx), 1)
    ->setCellValue(sprintf("C%s", $rowIdx), $row['name'])
    ->setCellValueExplicit(sprintf("D%s", $rowIdx), $phone1, PHPExcel_Cell_DataType::TYPE_STRING)
    ->setCellValueExplicit(sprintf("E%s", $rowIdx), $phone2, PHPExcel_Cell_DataType::TYPE_STRING)
    ->setCellValue(sprintf("F%s", $rowIdx), $address)
    ->setCellValue(sprintf("G%s", $rowIdx), $row['deliverymemo'])
    ->setCellValueExplicit(sprintf("H%s", $rowIdx), $row['orderid_sabangnet'], PHPExcel_Cell_DataType::TYPE_STRING)
    ->setCellValueExplicit(sprintf("I%s", $rowIdx), $box_price, PHPExcel_Cell_DataType::TYPE_NUMERIC) //기본운임
    ->setCellValueExplicit(sprintf("J%s", $rowIdx), $box_type, PHPExcel_Cell_DataType::TYPE_NUMERIC); //박스타입(1-극소, 2-소, 3-중, 4-대1, 7-대2, 5-특대)

    $objPHPExcel->getActiveSheet()->getStyle(sprintf("I%s", $rowIdx))->getNumberFormat()->setFormatCode("###0");
     
//  $objPHPExcel->setActiveSheetIndex(0)->getRowDimension($rowIdx)->setRowHeight(20);

    $rowIdx++;
}


// create file name
$fileName = '출고택배접수_'.$date;
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

//20210907 파일 로그
$cur = date("Ymd_his");
$objWriter->save('./files/delivery_excel_download_'.$cur.'.xlsx'); 

$objWriter->save('php://output');

// disconnect
$objPHPExcel->disconnectWorksheets();
$objPHPExcel->garbageCollect();

unset( $objWriter, $objPHPExcel );

$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='excel_dl_ship', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$temp_fileName' ");

exit;
?>