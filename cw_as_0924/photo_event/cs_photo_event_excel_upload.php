<?
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', false);

include("../def_inc.php");
include("../common_lib.php");

$mod  = M_CANCELLATION;
$menu = S_PHOTO_EVENT_LIST;
include("../header.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';

$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "cs_photo_event_list.php";
$table      = "cs_photo_event_orders";

if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] != 0) {
    $tools->alertJavaGo("업로드된 파일이 없거나 오류가 발생했습니다.", $return_url);
    exit;
}
$ext = strtolower(pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['xls', 'xlsx'])) {
    $tools->alertJavaGo("xls, xlsx 파일만 업로드 가능합니다.", $return_url);
    exit;
}

$objReader   = PHPExcel_IOFactory::createReaderForFile($_FILES['userfile']['tmp_name']);
$objPHPExcel = $objReader->load($_FILES['userfile']['tmp_name']);
$sheet       = $objPHPExcel->getActiveSheet();
$highestRow  = $sheet->getHighestRow();

$cnt_insert = 0;
$cnt_skip   = 0;

// 1행=헤더, 2행~데이터
// A=품목명, B=박스수량, C=받는분성명, D=받는분전화번호, E=받는분주소
// F=배송메시지, G=쇼핑몰, H=주문번호, I=아이디, J=구입일, K=제품명, L=메모
// M=상태(무시), N=관리자메모
for ($row = 2; $row <= $highestRow; $row++) {
    $col_product_gift   = trim($sheet->getCellByColumnAndRow(0, $row)->getValue());  // A
    $col_quantity       = trim($sheet->getCellByColumnAndRow(1, $row)->getValue());  // B
    $col_customer_name  = trim($sheet->getCellByColumnAndRow(2, $row)->getValue());  // C
    $col_customer_phone = trim($sheet->getCellByColumnAndRow(3, $row)->getValue());  // D
    $col_customer_addr  = trim($sheet->getCellByColumnAndRow(4, $row)->getValue());  // E
    $col_delivery_memo  = trim($sheet->getCellByColumnAndRow(5, $row)->getValue());  // F
    $col_shop_name      = trim($sheet->getCellByColumnAndRow(6, $row)->getValue());  // G
    $col_order_num      = trim($sheet->getCellByColumnAndRow(7, $row)->getValue());  // H
    $col_customer_id    = trim($sheet->getCellByColumnAndRow(8, $row)->getValue());  // I
    $col_purchase_date  = trim($sheet->getCellByColumnAndRow(9, $row)->getValue());  // J
    $col_product_name   = trim($sheet->getCellByColumnAndRow(10, $row)->getValue()); // K
    $col_memo           = trim($sheet->getCellByColumnAndRow(11, $row)->getValue()); // L
    // M(상태) 무시
    $col_pic_memo       = trim($sheet->getCellByColumnAndRow(13, $row)->getValue()); // N

    // 빈 행 스킵
    if ($col_product_gift == "" && $col_customer_name == "") continue;

    // 주문번호 중복 체크
    if ($col_order_num != "") {
        $exists = $db->object($table, "where order_num='" . mysqli_real_escape_string($db->db_conn, $col_order_num) . "'");
        if ($exists) { $cnt_skip++; continue; }
    }

    // 구입일 처리 (엑셀 숫자형 날짜 변환)
    if (is_numeric($col_purchase_date) && $col_purchase_date > 0) {
        $purchase_date = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($col_purchase_date));
    } else if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $col_purchase_date)) {
        $purchase_date = $col_purchase_date;
    } else {
        $purchase_date = "";
    }

    $quantity = (is_numeric($col_quantity) && $col_quantity > 0) ? (int)$col_quantity : 1;
    $purchase_date_val = ($purchase_date != "") ? "'$purchase_date'" : "NULL";

    $product_gift   = mysqli_real_escape_string($db->db_conn, $col_product_gift);
    $customer_name  = mysqli_real_escape_string($db->db_conn, $col_customer_name);
    $customer_phone = mysqli_real_escape_string($db->db_conn, $col_customer_phone);
    $customer_addr  = mysqli_real_escape_string($db->db_conn, $col_customer_addr);
    $delivery_memo  = mysqli_real_escape_string($db->db_conn, $col_delivery_memo);
    $shop_name      = mysqli_real_escape_string($db->db_conn, $col_shop_name);
    $order_num      = mysqli_real_escape_string($db->db_conn, $col_order_num);
    $customer_id    = mysqli_real_escape_string($db->db_conn, $col_customer_id);
    $product_name   = mysqli_real_escape_string($db->db_conn, $col_product_name);
    $memo           = mysqli_real_escape_string($db->db_conn, $col_memo);
    $pic_memo       = mysqli_real_escape_string($db->db_conn, $col_pic_memo);
    $pic_name       = mysqli_real_escape_string($db->db_conn, $ADMIN_NAME);

    $data = "product_gift='$product_gift', quantity=$quantity, customer_name='$customer_name',
    customer_phone='$customer_phone', customer_addr='$customer_addr', delivery_memo='$delivery_memo',
    shop_name='$shop_name', order_num='$order_num', customer_id='$customer_id',
    purchase_date=$purchase_date_val, product_name='$product_name',
    memo='$memo', pic_memo='$pic_memo', pic_name='$pic_name', status=0";

    if ($db->insert($table, $data)) {
        $cnt_insert++;
        $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_photo_event_upload', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='row=$row $product_gift'");
    }
}

$objPHPExcel->disconnectWorksheets();
unset($objPHPExcel);

$msg = "업로드 완료: 신규등록 {$cnt_insert}건";
if ($cnt_skip > 0) $msg .= ", 중복스킵 {$cnt_skip}건";

$tools->alertJavaGo($msg, $return_url);
include('../footer.php');
?>
