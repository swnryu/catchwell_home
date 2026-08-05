<?
session_start();

include("../common.php");
require("../check_session.php");
include("../def_inc.php");

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';

$table = "cs_sponsored_orders";

// 출고완료 페이지에서 호출 시 날짜 필터, 요청리스트 페이지에서는 처리중(status=0) 전체
$from_shipment = isset($_GET['shipment']) ? 1 : 0;  // 출고완료 다운로드
$from_approve  = isset($_GET['approve'])  ? 1 : 0;  // 승인완료 출고요청서 다운로드

if ($from_shipment) {
    // 출고완료(status=2) — 날짜 필터 적용
    $date_from    = isset($_GET['date_from'])    ? $_GET['date_from']    : date("Y-m-01");
    $date_to      = isset($_GET['date_to'])      ? $_GET['date_to']      : date("Y-m-d");
    $search_item  = isset($_GET['search_item'])  ? $_GET['search_item']  : "";
    $search_order = isset($_GET['search_order']) ? $_GET['search_order'] : "";
    $date_to2     = date("Y-m-d", strtotime($date_to . " +1 day"));

    $query = "select * from $table where reg_datetime between date('$date_from') and date('$date_to2') and status=2 ";
    if ($search_order) {
        $sq = mysqli_real_escape_string($db->db_conn, $search_order);
        if ($search_item) {
            $query .= "and $search_item like '%$sq%' ";
        } else {
            $query .= "and (product_name like '%$sq%' or customer_name like '%$sq%' or influencer_name like '%$sq%') ";
        }
    }
} else {
    // 승인완료(status=1) — 출고요청서 전체
    $query = "select * from $table where status=1 ";
}
$query .= "order by idx asc";

$result = mysqli_query($db->db_conn, $query);

// 템플릿 파일 로드 (1행 헤더 유지)
$templatePath = __DIR__ . "/협찬출고요청서.xlsx";
$objPHPExcel  = PHPExcel_IOFactory::load($templatePath);
$sheet        = $objPHPExcel->getActiveSheet();

// 2행 이후 기존 샘플 데이터 삭제
$highestRow = $sheet->getHighestRow();
if ($highestRow >= 2) {
    $sheet->removeRow(2, $highestRow - 1);
}

// 컬럼 매핑:
// A=발주날짜, B=모델, C=악세서리추가, D=액세서리(공란), E=수량
// F=구매처(공란), G=주문번호(공란), H=업체명(인플루언서명), I=수령자명
// J=송장번호, K=일반전화, L=핸드폰, M=주소, N=배송메세지
// O,U=주문번호(idx, CJ 배달상세 U열 고객주문번호로 반영됨), P(공란)

$sheet->getColumnDimension('C')->setWidth(8);

$rowIdx = 2;
while ($row = mysqli_fetch_object($result)) {
    $sheet->setCellValue("A$rowIdx", $row->request_date)
          ->setCellValue("B$rowIdx", $row->product_name)
          ->setCellValue("C$rowIdx", $row->accessory_name)
          ->setCellValue("D$rowIdx", "")
          ->setCellValue("E$rowIdx", (int)$row->quantity)
          ->setCellValue("F$rowIdx", $row->reason)
          ->setCellValue("G$rowIdx", "")
          ->setCellValue("H$rowIdx", $row->influencer_name)
          ->setCellValue("I$rowIdx", $row->customer_name)
          ->setCellValueExplicit("J$rowIdx", $row->delivery_num,    PHPExcel_Cell_DataType::TYPE_STRING)
          ->setCellValueExplicit("K$rowIdx", $row->customer_phone2, PHPExcel_Cell_DataType::TYPE_STRING)
          ->setCellValueExplicit("L$rowIdx", $row->customer_phone,  PHPExcel_Cell_DataType::TYPE_STRING)
          ->setCellValue("M$rowIdx", $row->customer_addr)
          ->setCellValue("N$rowIdx", $row->delivery_memo)
          ->setCellValueExplicit("O$rowIdx", $row->idx, PHPExcel_Cell_DataType::TYPE_STRING)
          ->setCellValue("P$rowIdx", "")
          ->setCellValueExplicit("U$rowIdx", $row->idx, PHPExcel_Cell_DataType::TYPE_STRING);
    $rowIdx++;
}

$fileName     = '협찬출고요청서_' . date("Ymd");
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

$db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_sponsored_excel_dl', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$fileName'");

exit;
?>
