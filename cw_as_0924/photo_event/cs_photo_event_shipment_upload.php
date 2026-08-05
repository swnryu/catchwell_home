<?
include("../def_inc.php");
$mod  = M_CANCELLATION;
$menu = S_PHOTO_EVENT_LIST;
include("../header.php");

$table      = "cs_photo_event_orders";
$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "cs_photo_event_list.php";

if (!isset($_FILES['userfile']) || $_FILES['userfile']['size'] <= 0) {
    $tools->errMsg("파일을 확인하세요.");
    exit;
}
if ($_FILES['userfile']['size'] > 1024 * 1024 * 5) {
    $tools->errMsg("5MB 이하 파일만 업로드 가능합니다.");
    exit;
}

require_once '../PHPExcel/Classes/PHPExcel.php';
require_once '../PHPExcel/Classes/PHPExcel/IOFactory.php';

try {
    $filePath = $_FILES['userfile']['tmp_name'];
    $objReader = PHPExcel_IOFactory::createReaderForFile($filePath);
    $objReader->setReadDataOnly(true);
    $objExcel = $objReader->load($filePath);
    $objExcel->setActiveSheetIndex(0);
    $sheet  = $objExcel->getActiveSheet();
    $maxRow = $sheet->getHighestRow();

    $cnt_suc = 0;

    // CJ택배 출고확정 양식 기준
    // H: 운송장번호, V: 받는분전화번호
    for ($i = 2; $i <= $maxRow; $i++) {
        $tracking_num = preg_replace('/[^0-9]/', '', $sheet->getCell('H' . $i)->getValue());
        $phone        = preg_replace('/[^0-9]/', '', $sheet->getCell('V' . $i)->getValue());

        if ($tracking_num == '' || $phone == '') continue;

        $phone_esc = mysqli_real_escape_string($db->db_conn, $phone);
        $where     = "where REPLACE(customer_phone, '-', '')='$phone_esc' AND status=0 ORDER BY reg_datetime DESC LIMIT 1";

        if ($db->cnt($table, $where) > 0) {
            $data = "delivery_num='$tracking_num', status=1 " . $where;
            if ($db->update($table, $data)) {
                $cnt_suc++;
                $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_photo_event_shipment', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='phone=$phone tracking=$tracking_num $ADMIN_NAME'");
            }
        }
    }

    $objExcel->disconnectWorksheets();
    unset($objExcel);

} catch (Exception $e) {
    $tools->alertJavaGo("파일을 읽는 중 오류가 발생하였습니다.", $return_url);
}

$tools->alertJavaGo("출고완료 처리: {$cnt_suc}건 업데이트하였습니다.", $return_url);

include('../footer.php');
?>
