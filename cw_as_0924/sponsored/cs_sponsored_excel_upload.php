<?
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', false);

include("../def_inc.php");
include("../common_lib.php");

$mod  = M_CANCELLATION;
$menu = S_SPONSORED_LIST;

include("../header.php");

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';

$return_url  = isset($_POST['return_url']) ? $_POST['return_url'] : "cs_sponsored_list.php";
$upload_type = isset($_POST['upload_type']) ? $_POST['upload_type'] : "file";
$table       = "cs_sponsored_orders";

$filePath = "";

if ($upload_type == "auto") {
    // 서버 루트의 고정 파일 사용
    $filePath = realpath("../협찬출고요청리스트.xlsx");
    if (!$filePath || !file_exists($filePath)) {
        $tools->alertJavaGo("파일을 찾을 수 없습니다: /협찬출고요청리스트.xlsx", $return_url);
        exit;
    }
} else {
    // 직접 업로드한 파일 사용
    if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] != 0) {
        $tools->alertJavaGo("업로드된 파일이 없거나 오류가 발생했습니다.", $return_url);
        exit;
    }
    $ext = strtolower(pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['xls', 'xlsx'])) {
        $tools->alertJavaGo("xls, xlsx 파일만 업로드 가능합니다.", $return_url);
        exit;
    }
    $filePath = $_FILES['userfile']['tmp_name'];
}

// xlsx 파일 읽기
$objReader   = PHPExcel_IOFactory::createReaderForFile($filePath);
$objPHPExcel = $objReader->load($filePath);
$sheet       = $objPHPExcel->getActiveSheet();
$highestRow  = $sheet->getHighestRow();

$cnt_insert = 0;
$cnt_skip   = 0;
$cnt_error  = 0;

// 3행부터 데이터 (1행=안내문, 2행=헤더)
for ($row = 3; $row <= $highestRow; $row++) {
    $col_reg_num      = trim($sheet->getCellByColumnAndRow(0, $row)->getValue());  // A: 등록번호
    $col_request_date = trim($sheet->getCellByColumnAndRow(1, $row)->getValue());  // B: 요청날짜
    $col_product_name = trim($sheet->getCellByColumnAndRow(2, $row)->getValue());  // C: 제품명
    $col_accessory    = trim($sheet->getCellByColumnAndRow(3, $row)->getValue());  // D: 악세서리추가
    $col_quantity     = trim($sheet->getCellByColumnAndRow(4, $row)->getValue());  // E: 수량
    $col_reason       = trim($sheet->getCellByColumnAndRow(5, $row)->getValue());  // F: 사유
    $col_influencer   = trim($sheet->getCellByColumnAndRow(6, $row)->getValue());  // G: 인플루언서명
    $col_company      = trim($sheet->getCellByColumnAndRow(7, $row)->getValue());  // H: 업체명
    $col_customer     = trim($sheet->getCellByColumnAndRow(8, $row)->getValue());  // I: 수령자명
    $col_delivery_num = trim($sheet->getCellByColumnAndRow(9, $row)->getValue());  // J: 송장번호
    $col_phone        = trim($sheet->getCellByColumnAndRow(10, $row)->getValue()); // K: 핸드폰
    $col_phone2       = trim($sheet->getCellByColumnAndRow(11, $row)->getValue()); // L: 일반전화
    $col_addr         = trim($sheet->getCellByColumnAndRow(12, $row)->getValue()); // M: 주소
    $col_delivery_memo= trim($sheet->getCellByColumnAndRow(13, $row)->getValue()); // N: 배송메세지

    // 제품명도 비어있으면 빈 행으로 간주하고 스킵
    if ($col_product_name == "" && $col_customer == "") {
        continue;
    }

    // 등록번호가 이미 있으면 이미 처리된 행 → 스킵
    if ($col_reg_num != "") {
        $cnt_skip++;
        continue;
    }

    // 요청날짜 처리 (숫자형 엑셀 날짜 → Y-m-d 변환)
    if (is_numeric($col_request_date) && $col_request_date > 0) {
        $unix = PHPExcel_Shared_Date::ExcelToPHP($col_request_date);
        $request_date = date("Y-m-d", $unix);
    } else if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $col_request_date)) {
        $request_date = $col_request_date;
    } else {
        $request_date = date("Y-m-d");
    }

    $quantity = (is_numeric($col_quantity) && $col_quantity > 0) ? (int)$col_quantity : 1;

    $product_name    = mysqli_real_escape_string($db->db_conn, $col_product_name);
    $accessory_name  = mysqli_real_escape_string($db->db_conn, $col_accessory);
    $reason          = mysqli_real_escape_string($db->db_conn, $col_reason);
    $influencer_name = mysqli_real_escape_string($db->db_conn, $col_influencer);
    $company_name    = mysqli_real_escape_string($db->db_conn, $col_company);
    $customer_name   = mysqli_real_escape_string($db->db_conn, $col_customer);
    $customer_phone  = mysqli_real_escape_string($db->db_conn, $col_phone);
    $customer_phone2 = mysqli_real_escape_string($db->db_conn, $col_phone2);
    $customer_addr   = mysqli_real_escape_string($db->db_conn, $col_addr);
    $delivery_memo   = mysqli_real_escape_string($db->db_conn, $col_delivery_memo);
    $delivery_num    = mysqli_real_escape_string($db->db_conn, $col_delivery_num);
    $pic_name        = mysqli_real_escape_string($db->db_conn, $ADMIN_NAME);

    $data = "request_date='$request_date', product_name='$product_name', accessory_name='$accessory_name',
    quantity=$quantity, reason='$reason', influencer_name='$influencer_name', company_name='$company_name',
    status=0, pic_name='$pic_name', customer_name='$customer_name',
    customer_phone='$customer_phone', customer_phone2='$customer_phone2',
    customer_addr='$customer_addr', delivery_memo='$delivery_memo', delivery_num='$delivery_num'";

    if ($db->insert($table, $data)) {
        $cnt_insert++;
        $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_sponsored_upload', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='row=$row $product_name'");
    } else {
        $cnt_error++;
    }
}

$objPHPExcel->disconnectWorksheets();
unset($objPHPExcel);

$msg = "업로드 완료: 신규등록 {$cnt_insert}건";
if ($cnt_skip > 0)  $msg .= ", 기등록(스킵) {$cnt_skip}건";
if ($cnt_error > 0) $msg .= ", 오류 {$cnt_error}건";

// Flow 알림 전송 (신규 등록 건이 있을 때만)
if ($cnt_insert > 0) {
    $flow_msg = "[협찬/샘플] 출고 요청 리스트가 등록되었습니다.\n"
              . "▸ 담당자: {$ADMIN_NAME}\n"
              . "▸ 신규등록: {$cnt_insert}건\n"
              . "▸ 시간: " . date("Y-m-d H:i:s");
    sendFlowMessage(4054022, $flow_msg);
}

$tools->alertJavaGo($msg, $return_url);

include('../footer.php');
?>
