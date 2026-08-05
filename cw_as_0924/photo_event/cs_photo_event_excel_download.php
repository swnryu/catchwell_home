<?
session_start();

include("../common.php");
require("../check_session.php");
include("../def_inc.php");

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';

$table = "cs_photo_event_orders";

$from_shipment = isset($_GET['shipment']) ? 1 : 0;

if ($from_shipment) {
    $date_from    = isset($_GET['date_from'])    ? $_GET['date_from']    : date("Y-m-01");
    $date_to      = isset($_GET['date_to'])      ? $_GET['date_to']      : date("Y-m-d");
    $search_item  = isset($_GET['search_item'])  ? $_GET['search_item']  : "";
    $search_order = isset($_GET['search_order']) ? $_GET['search_order'] : "";
    $date_to2     = date("Y-m-d", strtotime($date_to . " +1 day"));

    $query = "select * from $table where reg_datetime between date('$date_from') and date('$date_to2') and status=1 ";
    if ($search_order) {
        $sq = mysqli_real_escape_string($db->db_conn, $search_order);
        if ($search_item) {
            $query .= "and $search_item like '%$sq%' ";
        } else {
            $query .= "and (product_gift like '%$sq%' or customer_name like '%$sq%' or shop_name like '%$sq%') ";
        }
    }
} else {
    $query = "select * from $table where status=0 ";
}
$query .= "order by idx asc";

$result = mysqli_query($db->db_conn, $query);

// 협찬출고요청서.xlsx 템플릿 로드 (1행 헤더 유지)
$templatePath = realpath(__DIR__ . "/../sponsored/협찬출고요청서.xlsx");
$objPHPExcel  = PHPExcel_IOFactory::load($templatePath);
$sheet        = $objPHPExcel->getActiveSheet();

// 2행 이후 기존 샘플 데이터 삭제
$highestRow = $sheet->getHighestRow();
if ($highestRow >= 2) {
    $sheet->removeRow(2, $highestRow - 1);
}

// 협찬출고요청서.xlsx 컬럼 매핑 (포토이벤트 DB 기준):
// A=발주날짜    → purchase_date (구입일)
// B=모델        → product_gift  (품목명/사은품)
// C=악세서리추가 → product_name  (구매 제품명)
// D=액세서리    → (공란)
// E=수량        → quantity
// F=구매처      → shop_name    (쇼핑몰)
// G=주문번호    → order_num
// H=업체명      → customer_id  (고객 아이디)
// I=수령자명    → customer_name
// J=송장번호    → delivery_num
// K=일반전화    → (공란)
// L=핸드폰      → customer_phone
// M=주소        → customer_addr
// N=배송메세지  → delivery_memo
// O~P          → (공란)

$rowIdx = 2;
while ($row = mysqli_fetch_object($result)) {
    $sheet->setCellValue("A$rowIdx", $row->purchase_date)
          ->setCellValue("B$rowIdx", $row->product_gift)
          ->setCellValue("C$rowIdx", "")
          ->setCellValue("D$rowIdx", "")
          ->setCellValue("E$rowIdx", (int)$row->quantity)
          ->setCellValue("F$rowIdx", $row->shop_name)
          ->setCellValueExplicit("G$rowIdx", $row->order_num,      PHPExcel_Cell_DataType::TYPE_STRING)
          ->setCellValue("H$rowIdx", $row->customer_id)
          ->setCellValue("I$rowIdx", $row->customer_name)
          ->setCellValueExplicit("J$rowIdx", $row->delivery_num,   PHPExcel_Cell_DataType::TYPE_STRING)
          ->setCellValue("K$rowIdx", "")
          ->setCellValueExplicit("L$rowIdx", $row->customer_phone, PHPExcel_Cell_DataType::TYPE_STRING)
          ->setCellValue("M$rowIdx", $row->customer_addr)
          ->setCellValue("N$rowIdx", $row->delivery_memo)
          ->setCellValue("O$rowIdx", "")
          ->setCellValue("P$rowIdx", "");
    $rowIdx++;
}

$fileName        = '포토이벤트출고요청서_' . date("Ymd");
$fileNameEncoded = mb_convert_encoding($fileName, 'euc-kr', 'UTF-8');

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileNameEncoded . '.xlsx"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

$objPHPExcel->disconnectWorksheets();
unset($objWriter, $objPHPExcel);

$db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_photo_event_excel_dl', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$fileName'");

exit;
?>
