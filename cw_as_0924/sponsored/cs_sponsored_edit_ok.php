<?
error_reporting(E_ALL);
ini_set('display_errors', false);
ini_set('display_startup_errors', false);

include("../def_inc.php");
include("../common_lib.php");

$mod  = M_CANCELLATION;
$menu = S_SPONSORED_LIST;

include("../header.php");

$db_name    = "cs_sponsored_orders";
$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "cs_sponsored_list.php";
$idx        = isset($_POST['idx']) ? (int)$_POST['idx'] : 0;
$mode       = isset($_POST['mode']) ? $_POST['mode'] : "new";

$request_date    = isset($_POST['request_date'])    ? mysqli_real_escape_string($db->db_conn, $_POST['request_date'])    : "";
$product_name    = isset($_POST['product_name'])    ? mysqli_real_escape_string($db->db_conn, $_POST['product_name'])    : "";
$accessory_name  = isset($_POST['accessory_name'])  ? mysqli_real_escape_string($db->db_conn, $_POST['accessory_name'])  : "";
$quantity        = isset($_POST['quantity'])         ? (int)$_POST['quantity']                                            : 1;
$reason          = isset($_POST['reason'])           ? mysqli_real_escape_string($db->db_conn, $_POST['reason'])          : "";
$influencer_name = isset($_POST['influencer_name']) ? mysqli_real_escape_string($db->db_conn, $_POST['influencer_name']) : "";
$company_name    = isset($_POST['company_name'])    ? mysqli_real_escape_string($db->db_conn, $_POST['company_name'])    : "";
$status          = isset($_POST['status'])           ? (int)$_POST['status']                                              : 0;
$pic_memo        = isset($_POST['pic_memo'])         ? mysqli_real_escape_string($db->db_conn, $_POST['pic_memo'])        : "";
$pic_name        = isset($_POST['pic_name'])         ? mysqli_real_escape_string($db->db_conn, $_POST['pic_name'])        : "";
$customer_name   = isset($_POST['customer_name'])   ? mysqli_real_escape_string($db->db_conn, $_POST['customer_name'])   : "";
$customer_phone  = isset($_POST['customer_phone'])  ? mysqli_real_escape_string($db->db_conn, $_POST['customer_phone'])  : "";
$customer_phone2 = isset($_POST['customer_phone2']) ? mysqli_real_escape_string($db->db_conn, $_POST['customer_phone2']) : "";
$customer_addr   = isset($_POST['customer_addr'])   ? mysqli_real_escape_string($db->db_conn, $_POST['customer_addr'])   : "";
$delivery_memo   = isset($_POST['delivery_memo'])   ? mysqli_real_escape_string($db->db_conn, $_POST['delivery_memo'])   : "";
$delivery_num    = isset($_POST['delivery_num'])    ? mysqli_real_escape_string($db->db_conn, $_POST['delivery_num'])    : "";

$request_date_val = ($request_date != "") ? "'$request_date'" : "NULL";

if ($mode == "new") {
    $data = "request_date=$request_date_val, product_name='$product_name', accessory_name='$accessory_name',
    quantity=$quantity, reason='$reason', influencer_name='$influencer_name', company_name='$company_name',
    status=0, pic_name='$pic_name', pic_memo='$pic_memo',
    customer_name='$customer_name', customer_phone='$customer_phone', customer_phone2='$customer_phone2',
    customer_addr='$customer_addr', delivery_memo='$delivery_memo', delivery_num='$delivery_num'";

    if ($db->insert($db_name, $data)) {
        $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_sponsored_new', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment=''");
        $tools->alertJavaGo("등록 되었습니다.", $return_url);
    } else {
        $tools->errMsg("데이터베이스 에러 발생(1)");
    }
}
else if ($mode == "edit") {
    $data = "request_date=$request_date_val, product_name='$product_name', accessory_name='$accessory_name',
    quantity=$quantity, reason='$reason', influencer_name='$influencer_name', company_name='$company_name',
    status=$status, pic_name='$pic_name', pic_memo='$pic_memo',
    customer_name='$customer_name', customer_phone='$customer_phone', customer_phone2='$customer_phone2',
    customer_addr='$customer_addr', delivery_memo='$delivery_memo', delivery_num='$delivery_num'
    where idx=$idx";

    if ($db->update($db_name, $data)) {
        $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_sponsored_edit', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $ADMIN_NAME'");
        $tools->alertJavaGo("수정 되었습니다.", $return_url);
    } else {
        $tools->errMsg("데이터베이스 에러 발생(2)");
    }
}
else if ($mode == "del") {
    if ($idx == 0) {
        $tools->errMsg("삭제할 데이터가 없습니다.");
    } else {
        if ($db->delete($db_name, "where idx=$idx")) {
            $db->insert("admin_log", "userid='$_SESSION[ADMIN_USERID]', contents='cs_sponsored_del', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $ADMIN_NAME'");
            $tools->alertJavaGo("삭제 되었습니다.", $return_url);
        } else {
            $tools->errMsg("데이터베이스 에러 발생(3)");
        }
    }
}

include('../footer.php');
?>
