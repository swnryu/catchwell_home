<?
error_reporting(E_ALL);
ini_set('display_errors', false);
ini_set('display_startup_errors', false);

include("../def_inc.php");
include("../common_lib.php");

$mod  = M_CANCELLATION;
$menu = S_PHOTO_EVENT_LIST;
include("../header.php");

$db_name    = "cs_photo_event_orders";
$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "cs_photo_event_list.php";
$idx        = isset($_POST['idx']) ? (int)$_POST['idx'] : 0;
$mode       = isset($_POST['mode']) ? $_POST['mode'] : "new";

$product_gift   = isset($_POST['product_gift'])   ? mysqli_real_escape_string($db->db_conn, $_POST['product_gift'])   : "";
$quantity       = isset($_POST['quantity'])        ? (int)$_POST['quantity']                                           : 1;
$shop_name      = isset($_POST['shop_name'])       ? mysqli_real_escape_string($db->db_conn, $_POST['shop_name'])      : "";
$order_num      = isset($_POST['order_num'])       ? mysqli_real_escape_string($db->db_conn, $_POST['order_num'])      : "";
$customer_id    = isset($_POST['customer_id'])     ? mysqli_real_escape_string($db->db_conn, $_POST['customer_id'])    : "";
$purchase_date  = isset($_POST['purchase_date'])   ? mysqli_real_escape_string($db->db_conn, $_POST['purchase_date'])  : "";
$product_name   = isset($_POST['product_name'])    ? mysqli_real_escape_string($db->db_conn, $_POST['product_name'])   : "";
$memo           = isset($_POST['memo'])            ? mysqli_real_escape_string($db->db_conn, $_POST['memo'])           : "";
$status         = isset($_POST['status'])          ? (int)$_POST['status']                                             : 0;
$pic_memo       = isset($_POST['pic_memo'])        ? mysqli_real_escape_string($db->db_conn, $_POST['pic_memo'])       : "";
$pic_name       = isset($_POST['pic_name'])        ? mysqli_real_escape_string($db->db_conn, $_POST['pic_name'])       : "";
$customer_name  = isset($_POST['customer_name'])   ? mysqli_real_escape_string($db->db_conn, $_POST['customer_name'])  : "";
$customer_phone = isset($_POST['customer_phone'])  ? mysqli_real_escape_string($db->db_conn, $_POST['customer_phone']) : "";
$customer_addr  = isset($_POST['customer_addr'])   ? mysqli_real_escape_string($db->db_conn, $_POST['customer_addr'])  : "";
$delivery_memo  = isset($_POST['delivery_memo'])   ? mysqli_real_escape_string($db->db_conn, $_POST['delivery_memo'])  : "";
$delivery_num   = isset($_POST['delivery_num'])    ? mysqli_real_escape_string($db->db_conn, $_POST['delivery_num'])   : "";

$purchase_date_val = ($purchase_date != "") ? "'$purchase_date'" : "NULL";

$common_fields = "product_gift='$product_gift', quantity=$quantity, shop_name='$shop_name',
    order_num='$order_num', customer_id='$customer_id', purchase_date=$purchase_date_val,
    product_name='$product_name', memo='$memo', pic_name='$pic_name', pic_memo='$pic_memo',
    customer_name='$customer_name', customer_phone='$customer_phone',
    customer_addr='$customer_addr', delivery_memo='$delivery_memo', delivery_num='$delivery_num'";

if ($mode == "new") {
    $data = "$common_fields, status=0";
    if ($db->insert($db_name, $data)) {
        $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_photo_event_new', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment=''");
        $tools->alertJavaGo("등록 되었습니다.", $return_url);
    } else {
        $tools->errMsg("데이터베이스 에러 발생(1)");
    }
} else if ($mode == "edit") {
    $data = "$common_fields, status=$status where idx=$idx";
    if ($db->update($db_name, $data)) {
        $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_photo_event_edit', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $ADMIN_NAME'");
        $tools->alertJavaGo("수정 되었습니다.", $return_url);
    } else {
        $tools->errMsg("데이터베이스 에러 발생(2)");
    }
} else if ($mode == "del") {
    if ($idx == 0) {
        $tools->errMsg("삭제할 데이터가 없습니다.");
    } else {
        if ($db->delete($db_name, "where idx=$idx")) {
            $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_photo_event_del', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $ADMIN_NAME'");
            $tools->alertJavaGo("삭제 되었습니다.", $return_url);
        } else {
            $tools->errMsg("데이터베이스 에러 발생(3)");
        }
    }
}

include('../footer.php');
?>
