<?
include("../def_inc.php");
$mod  = M_CANCELLATION;
$menu = S_SPONSORED_LIST;
include("../header.php");

$table      = "cs_sponsored_orders";
$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "cs_sponsored_list.php";

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
    // H: 운송장번호, S: 고객주문번호(=cs_sponsored_orders.idx, 출고요청서 O/U열로 보낸 값이 반영되어 돌아옴)
    for ($i = 2; $i <= $maxRow; $i++) {
        $tracking_num = preg_replace('/[^0-9]/', '', $sheet->getCell('H' . $i)->getValue());
        $order_idx    = preg_replace('/[^0-9]/', '', $sheet->getCell('S' . $i)->getValue());

        if ($tracking_num == '' || $order_idx == '') continue;

        $where = "where idx='$order_idx' AND status=1";

        if ($db->cnt($table, $where) > 0) {
            $data = "delivery_num='$tracking_num', status=2 " . $where;
            if ($db->update($table, $data)) {
                $cnt_suc++;
                $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_sponsored_shipment', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='idx=$order_idx tracking=$tracking_num $ADMIN_NAME'");
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
